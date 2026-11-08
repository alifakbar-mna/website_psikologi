<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Upload terlalu besar — Case Management System</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background: #f6f8f5;
            color: #334155;
            font-family: system-ui, sans-serif;
        }

        main {
            max-width: 520px;
            padding: 32px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            box-shadow: 0 8px 30px #33415508;
        }

        .code {
            color: #a16207;
            font-weight: 700;
            font-size: 14px;
        }

        h1 {
            font-size: 24px;
            margin: 12px 0;
        }

        p {
            line-height: 1.7;
            font-size: 14px;
        }

        a {
            display: inline-block;
            margin-top: 12px;
            background: #607554;
            color: white;
            text-decoration: none;
            padding: 12px 18px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <main>
        <div class="code">413 · Upload terlalu besar</div>

        <h1>Kurangi ukuran upload</h1>

        <p>{{ $message }}</p>

        <p>
            Permintaan ini belum diproses.
            Setelah kembali ke formulir, periksa isian dan pilih ulang
            PDF yang diperlukan.
        </p>

        <a href="{{ route('lecturer.upload') }}">
            Kembali ke upload laporan
        </a>
    </main>
</body>
</html>