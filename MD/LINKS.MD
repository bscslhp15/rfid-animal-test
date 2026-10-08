# QR Test Links

## Local app
- http://127.0.0.1:8000/dashboard

## Public Cloudflare tunnel
- https://gabriel-another-borough-studio.trycloudflare.com

## Public QR record route pattern
- https://gabriel-another-borough-studio.trycloudflare.com/t/{token}

## Example QR flow
1. Open the dashboard locally at http://127.0.0.1:8000/dashboard
2. Register or view an animal with a QR tag
3. Scan the generated QR using a phone
4. The phone should open the public record page through the Cloudflare tunnel
5. Confirm the QR link loads the route at /t/{token}

## Notes
- The Laravel app is running with PHP 8.4.4.
- The Cloudflare tunnel is temporary and for testing only.
- If the tunnel URL changes, update this file after restarting it.
