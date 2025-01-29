<!DOCTYPE html>
<html>

<head>
    <title>Activating Subscription - {{ config('app.name') }}</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 dark:bg-gray-900">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="w-full max-w-sm p-8 text-center bg-white rounded-lg shadow-lg dark:bg-gray-800">
            <!-- Success Icon -->
            <div class="w-16 h-16 mx-auto mb-4">
                <svg class="w-full h-full text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>

            <!-- Title -->
            <h2 class="mb-2 text-2xl font-bold text-gray-900 dark:text-white">
                Payment Successful!
            </h2>

            <!-- Message -->
            <p class="mb-6 text-gray-600 dark:text-gray-300">
                We're activating your subscription. This may take a small moment.
            </p>

            <!-- Loading Animation -->
            <div class="flex items-center justify-center gap-2">
                <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0s"></div>
                <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
            </div>
        </div>
    </div>

    <script>
        setTimeout(function() {
            window.close();
        }, 20000);
    </script>
</body>

</html>
