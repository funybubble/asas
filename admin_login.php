<?php
// Simple admin login page that posts to login.php
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin prijava | Čebelarstvo Cigoj</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-amber-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold mb-4 text-amber-900">Administracija - Prijava</h1>
    <!-- target="_top" attempts to replace the top-level browsing context when inside an iframe/preview -->
    <form id="loginForm" class="space-y-4" method="post" action="login.php" target="_top">
            <div>
                <label class="block text-sm font-medium text-gray-700">Uporabniško ime</label>
                <input id="username" name="username" type="text" required class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:ring-amber-500 focus:border-amber-500" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Geslo</label>
                <input id="password" name="password" type="password" required class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:ring-amber-500 focus:border-amber-500" />
            </div>
            <div>
                <button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-amber-700 hover:bg-amber-800">Prijava</button>
            </div>
            <div id="error" class="text-red-600 text-sm"></div>
        </form>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Ročno prilepi žeton (če preview blokira piškotke)</label>
                    <div class="flex mt-2">
                        <input id="pasteToken" class="flex-1 rounded border-gray-300 shadow-sm p-2" placeholder="Prilepite JWT sem..." />
                        <button id="applyToken" class="ml-2 bg-amber-600 text-white px-3 py-2 rounded">Uporabi</button>
                    </div>
                </div>
                    <div class="mt-4 text-sm text-gray-600">
                        <p>If the preview still blocks navigation, you can open the dashboard manually:</p>
                        <div class="mt-2">
                            <a href="backend.php" target="_blank" class="inline-block bg-amber-100 border border-amber-300 text-amber-700 px-3 py-2 rounded">Open dashboard in new tab</a>
                        </div>
                    </div>
    </div>

        <script>
        // Minimal JS: keep paste-token helper and top-window redirect attempt for special previews.
        document.getElementById('applyToken').addEventListener('click', function(e) {
            const token = document.getElementById('pasteToken').value.trim();
            if (!token) return;
            const expires = new Date(Date.now() + 7*24*60*60*1000).toUTCString();
            try {
                document.cookie = `refresh_token=${token}; expires=${expires}; path=/; SameSite=None; Secure`;
            } catch (e) {
                // ignore
            }
            try {
                if (window.top && window.top !== window) {
                    window.top.location.replace('backend.php');
                    return;
                }
            } catch (e) {}
            window.location.replace('backend.php');
        });
        </script>
</body>
</html>
