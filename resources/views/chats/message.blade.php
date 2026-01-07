@extends('layouts.app') 
@section('app')


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Messages</title>
</head>
<body>
    <h1>Chat Messages</h1>
    <table border="1">
        <thead>
            <tr>
                <th>Sender</th>
                <th>Message</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            @foreach($messagesData as $message)
                <tr>
                    <td>{{ $message['sender'] }}</td>
                    <td>{{ $message['text'] }}</td>
                    <td>{{ \Carbon\Carbon::parse($message['timestamp'])->format('Y-m-d H:i:s') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
