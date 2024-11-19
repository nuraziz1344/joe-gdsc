<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>coba firebase </title>
    <style></style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th>id</th>
                <th>author</th>
                <th>title</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reference as $key => $item)
            <tr>
                <td>{{ $key }}</td>
                <td>{{ $item['author'] }}</td>
                <td>{{ $item['title'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>  