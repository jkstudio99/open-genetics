# 🧬 Genetic SDK

> Connect your frontend to the OpenGenetics API with **one line of code**.

## Installation

### Option 1: npm (React / Node.js projects)

```bash
npm install @opengenetics/sdk
```

### Option 2: CDN / Script Tag (Vanilla JS)

```html
<script src="https://your-domain.com/open-genetics/sdk/genetics.min.js"></script>
```

### Option 3: Copy to your project

```bash
cp /open-genetics/sdk/genetics.min.js ./your-project/
```

## Quick Start

### Vanilla JS

```javascript
Genetics.init({ baseUrl: '/open-genetics/public/api', locale: 'th' });

// Login in one line
const result = await Genetics.login('admin@opengenetics.io', 'password');

// Translate
Genetics.t('auth.login'); // → "เข้าสู่ระบบ"

// Switch theme
Genetics.setTheme('dark');
```

### React

```jsx
import { GeneticsProvider, useGenetics } from '@opengenetics/sdk/react/useGenetics';

// Wrap your app
<GeneticsProvider baseUrl="/open-genetics/public/api">
  <App />
</GeneticsProvider>

// Use in any component
const { login, t, setLocale, setTheme } = useGenetics();
```

## Full Documentation

See [08-GENETIC-SDK.md](../docs/08-GENETIC-SDK.md)
