<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Flex:wght@100;200;300;400;500;600;700;800;900;1000&display=swap" rel="stylesheet" />
    <title>Info Login - MONARK</title>
</head>
<body>
    <div style="align-items: center; justify-content: center; gap: 2rem; width: 100%; background-color: #16130E; color: #CDC5BD; font-family: 'Roboto Flex', sans-serif; padding: 16px;">
        <div style="color: #CDC5BD; opacity: 80%; font-size: 0.875rem; margin-bottom: 16px;">Monark</div>
        <div style="align-items: center; justify-content: center;">
            <div style="color: #CDC5BD; opacity: 80%; font-size: 1.875rem;">Email Anda: </div>
            <div style="font-weight: 700; font-size: 5rem;">{{ $data['email'] }}</div>
            <div style="color: #CDC5BD; opacity: 80%; font-size: 1.875rem;">Password: </div>
            <div style="font-weight: 700; font-size: 5rem;">{{ $data['password'] }}</div>
        </div>
        <div style="color: #CDC5BD; opacity: 80%; font-size: 0.875rem;">Silahkan ganti password anda setelah login.</div>
    </div>
</body>
</html>