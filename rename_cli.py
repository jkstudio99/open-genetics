import os

search_text = 'bin/genetics'
replace_text = 'add/genetics'

directories = [
    'add', # The folder after moving
    'docs',
    'public'
]

files_to_check = [
    'composer.json',
    'README.md',
    'CONTRIBUTING.md'
]

def replace_in_file(filepath):
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        if search_text in content:
            content = content.replace(search_text, replace_text)
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            print(f"Updated: {filepath}")
    except Exception as e:
        print(f"Failed to process {filepath}: {e}")

# Process specific files
for f in files_to_check:
    if os.path.exists(f):
        replace_in_file(f)

# Process directories recursively
for d in directories:
    if not os.path.exists(d):
        continue
    for root, dirs, files in os.walk(d):
        for file in files:
            # Skip media/binary files
            if file.endswith(('.png', '.svg', '.jpg', '.woff2', '.ttf', '.pdf', '.json', '.md', '.html', '.css', '.js', 'genetics')):
                filepath = os.path.join(root, file)
                replace_in_file(filepath)
