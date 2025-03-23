<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
    <style>
        /* Base styles matching CKEditor output */
        .email-container {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            line-height: 1.6;
            color: #2d3748;
            max-width: 800px;
            margin: 0 auto;
            padding: 2.5rem;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Headings */
        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            color: #2c5282;
            margin: 1.5em 0 0.5em;
            font-weight: 600;
            letter-spacing: -0.025em;
            line-height: 1.3;
        }

        h1 {
            font-size: 2rem;
        }

        h2 {
            font-size: 1.75rem;
        }

        h3 {
            font-size: 1.5rem;
        }

        /* Paragraphs */
        p {
            margin: 0 0 1rem;
            font-size: 1rem;
        }

        /* Images */
        img {
            max-width: 100%;
            height: auto;
            margin: 1rem 0;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 1.5rem 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
        }

        th,
        td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        /* Lists */
        ul,
        ol {
            margin: 1rem 0;
            padding-left: 2rem;
        }

        li {
            margin-bottom: 0.5rem;
        }

        /* Blockquotes */
        blockquote {
            margin: 1.5rem 0;
            padding: 1rem 1.5rem;
            background-color: #f8f9fa;
            border-left: 4px solid #3f51b5;
            color: #666;
            font-style: italic;
        }

        /* Links */
        a {
            color: #3f51b5;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        /* Code blocks */
        pre {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 4px;
            overflow-x: auto;
            margin: 1.5rem 0;
        }

        code {
            font-family: 'Courier New', Courier, monospace;
            font-size: 0.9rem;
        }

        /* Responsive design */
        @media (max-width: 640px) {
            .email-container {
                padding: 1rem;
            }

            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>

<body>
    <div class="email-container">
        <header
            style="border-bottom: 2px solid #4299e1; padding-bottom: 1.5rem; margin-bottom: 2.5rem; background: linear-gradient(to right, #4299e1, #667eea); margin: -2.5rem -2.5rem 2.5rem -2.5rem; padding: 2.5rem; border-radius: 8px 8px 0 0;">
            <h1 style="margin: 0 0 0.5rem; color: #ffffff; font-size: 1.875rem;">{{ $subject }}</h1>
            <div style="color: rgba(255, 255, 255, 0.9); font-size: 0.95rem;">
                <p style="margin: 0;">From: {{ $name }} &lt;{{ $email }}&gt;</p>
                <p style="margin: 0.5rem 0 0;">Sent: {{ now()->format('d/m/Y H:i:s') }}</p>
            </div>
        </header>

        <main>
            <div class="email-content" style="line-height: 1.8; color: #4a5568;">
                {!! $messageContent !!}
            </div>
        </main>
    </div>
</body>

</html>