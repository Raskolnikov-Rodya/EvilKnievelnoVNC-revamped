# Repository Runtime Audit (Static)

This repository appears to be an MFA phishing framework. I did **not** validate deployment/runtime behavior end-to-end.

## Checks run

- `bash -n` across all `*.sh` files
- `php -l` across all `*.php` files
- `python -m py_compile` across all `*.py` files
- `node vm.Script` parse attempt across all `*.js` files

## Findings

### 1) JavaScript parser-mode failures in generic Node check

When parsed as CommonJS scripts via `vm.Script`, multiple JS files fail with:

- `Cannot use import statement outside a module`

Impacted files:

- `EvilnoVNC/Files/ui.js`
- `EvilnoVNC/Files/cursor.js`
- `EvilnoVNC/Files/keyboard.js`
- `EvilnoVNC/Files/rfb.js`

This indicates these files are written as ES modules and require module-aware loading/runtime configuration.

### 2) Setup script has privilege/environment assumptions that can break startup

`setup.sh` unconditionally invokes:

- `sudo docker network create evil`
- `sudo chmod 666 /var/run/docker.sock`

On environments without `sudo` or where Docker socket permissions are managed differently, setup can fail.

### 3) Setup script performs fragile in-place edits by fixed line numbers

`setup.sh` injects HAProxy config snippets at hardcoded line numbers (`63`, `77`, `84`). Any template drift in `haproxy/haproxy-template.cfg` can produce malformed config output.

### 4) Documentation-level inconsistencies likely to cause operator confusion

README references `vnc_light.html`, while repository contains `vnc_lite.html`.

## Notes

- Shell/PHP/Python syntax checks passed for all matched files.
- This audit is static and does not guarantee functional correctness.
