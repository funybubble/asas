---
title: Aa21123a
emoji: 🐨
colorFrom: red
colorTo: gray
sdk: static
pinned: false
---

Check out the configuration reference at https://huggingface.co/docs/hub/spaces-config-reference
"# cbc"

# Čebelarstvo Cigoj Admin Panel

## Admin Usage
- Login: `/admin_login.php` (username: `admin`, password: your set password)
- Dashboard: `/backend.php` (requires login)
- Logout: `/logout.php` (clears session)

## Troubleshooting
- If you get redirected to login repeatedly, check your database for the correct admin user and role.
- If preview/iframe blocks navigation, use the "Open dashboard in new tab" button after login.

## Security
- Remove `DISABLE_RATE_LIMIT` for production.
- Set cookies to `secure` and `SameSite=None` for HTTPS deployments.
- Change the hardcoded JWT secret in `config.php` before going live.
