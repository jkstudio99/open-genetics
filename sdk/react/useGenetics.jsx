/**
 * 🧬 OpenGenetics — React SDK
 * useGenetics() Hook
 * 
 * Provides auth state, i18n translation, theme switching,
 * and API fetch for React applications.
 * 
 * Usage:
 *   import { GeneticsProvider, useGenetics } from './useGenetics';
 *   
 *   // Wrap your app:
 *   <GeneticsProvider baseUrl="/open-genetics/public/api">
 *     <App />
 *   </GeneticsProvider>
 *   
 *   // In any component:
 *   const { login, t, setLocale, isAuthenticated } = useGenetics();
 * 
 * @version 1.0.0
 */

import { createContext, useContext, useState, useEffect, useCallback } from 'react';

// ─── Context ─────────────────────────────────────────

const GeneticsContext = createContext(null);

// ─── Provider ────────────────────────────────────────

export function GeneticsProvider({ children, baseUrl = '/open-genetics/public/api' }) {
    const [token, setToken] = useState(() => localStorage.getItem('og_token'));
    const [user, setUser] = useState(() => {
        const saved = localStorage.getItem('og_user');
        return saved ? JSON.parse(saved) : null;
    });
    const [locale, setLocaleState] = useState(() => localStorage.getItem('og_locale') || 'en');
    const [translations, setTranslations] = useState({});
    const [theme, setThemeState] = useState(() => localStorage.getItem('og_theme') || 'system');
    const [loading, setLoading] = useState(false);

    // ── API Fetch ────────────────────────────────────

    const apiFetch = useCallback(async (endpoint, options = {}) => {
        const url = `${baseUrl}/${endpoint.replace(/^\//, '')}`;

        const headers = {
            'Content-Type': 'application/json',
            'X-Locale': locale,
            ...(options.headers || {}),
        };

        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        const fetchOptions = {
            method: options.method || 'GET',
            headers,
        };

        if (options.body && fetchOptions.method !== 'GET') {
            fetchOptions.body = JSON.stringify(options.body);
        }

        const response = await fetch(url, fetchOptions);
        const data = await response.json();

        if (response.status === 401) {
            setToken(null);
            setUser(null);
            localStorage.removeItem('og_token');
            localStorage.removeItem('og_user');
        }

        return data;
    }, [baseUrl, token, locale]);

    // ── Auth ─────────────────────────────────────────

    const login = useCallback(async (email, password) => {
        setLoading(true);
        try {
            const res = await apiFetch('auth/login', {
                method: 'POST',
                body: { email, password },
            });

            if (res.success && res.data.token) {
                setToken(res.data.token);
                setUser(res.data.user);
                localStorage.setItem('og_token', res.data.token);
                localStorage.setItem('og_user', JSON.stringify(res.data.user));
            }

            return res;
        } finally {
            setLoading(false);
        }
    }, [apiFetch]);

    const register = useCallback(async (email, password, roleId = 3) => {
        setLoading(true);
        try {
            const res = await apiFetch('auth/register', {
                method: 'POST',
                body: { email, password, role_id: roleId },
            });

            if (res.success && res.data.token) {
                setToken(res.data.token);
                setUser(res.data.user);
                localStorage.setItem('og_token', res.data.token);
                localStorage.setItem('og_user', JSON.stringify(res.data.user));
            }

            return res;
        } finally {
            setLoading(false);
        }
    }, [apiFetch]);

    const logout = useCallback(() => {
        setToken(null);
        setUser(null);
        localStorage.removeItem('og_token');
        localStorage.removeItem('og_user');
    }, []);

    const getProfile = useCallback(async () => {
        return apiFetch('auth/profile');
    }, [apiFetch]);

    // ── i18n ─────────────────────────────────────────

    const loadTranslations = useCallback(async (loc) => {
        try {
            const res = await apiFetch(`i18n?lang=${loc}`);
            if (res.success) {
                setTranslations(res.data.translations);
            }
        } catch (e) {
            console.warn('🧬 Failed to load translations:', e);
        }
    }, [apiFetch]);

    const setLocale = useCallback(async (loc) => {
        setLocaleState(loc);
        localStorage.setItem('og_locale', loc);
        await loadTranslations(loc);
    }, [loadTranslations]);

    const t = useCallback((key, params = {}) => {
        let value = translations;
        const parts = key.split('.');

        for (const part of parts) {
            if (value && typeof value === 'object' && part in value) {
                value = value[part];
            } else {
                return key;
            }
        }

        if (typeof value !== 'string') return key;

        Object.keys(params).forEach(k => {
            value = value.replace(`:${k}`, params[k]);
        });

        return value;
    }, [translations]);

    // ── Theme ────────────────────────────────────────

    const setTheme = useCallback((mode) => {
        setThemeState(mode);
        localStorage.setItem('og_theme', mode);

        let resolved = mode;
        if (mode === 'system') {
            resolved = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }

        document.documentElement.setAttribute('data-theme', resolved);
        document.documentElement.classList.toggle('dark', resolved === 'dark');
    }, []);

    // ── Effects ──────────────────────────────────────

    useEffect(() => {
        loadTranslations(locale);
    }, []); // Load on mount

    useEffect(() => {
        setTheme(theme);
    }, [theme, setTheme]);

    // ── Context Value ────────────────────────────────

    const value = {
        // Auth
        login,
        register,
        logout,
        getProfile,
        isAuthenticated: !!token,
        user,
        token,
        loading,

        // API
        fetch: apiFetch,

        // i18n
        t,
        locale,
        setLocale,
        translations,

        // Theme
        theme,
        setTheme,
    };

    return (
        <GeneticsContext.Provider value={value}>
            {children}
        </GeneticsContext.Provider>
    );
}

// ─── Hook ────────────────────────────────────────────

export function useGenetics() {
    const ctx = useContext(GeneticsContext);
    if (!ctx) {
        throw new Error('useGenetics() must be used inside <GeneticsProvider>');
    }
    return ctx;
}

export default useGenetics;
