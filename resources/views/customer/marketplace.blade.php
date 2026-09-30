<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rentify Marketplace</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f0f9ff; padding: 40px; text-align: center; }
        .card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); display: inline-block; }
        h1 { color: #0284c7; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Selamat Datang di Marketplace Rentify!</h1>
        <p>Kamu berhasil mendaftar dan login sebagai <b>Customer (Penyewa)</b>.</p>
        <p>Di sini nanti kamu bisa melihat daftar barang yang disewakan oleh vendor.</p>
        <hr>
        <form action="/logout" method="POST" style="display:inline;">
            @csrf
            <button type="submit" style="color: red; background: none; border: none; font-weight: bold; cursor: pointer;">Keluar / Logout</button>
        </form>
    </div>
</body>
</html>