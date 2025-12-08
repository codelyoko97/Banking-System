<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>

<body>
  <h2>{{ $title }}</h2>

  <table border="1" width="100%" cellspacing="0" cellpadding="5">
    <thead>
      <tr>
        <th>ID</th>
        <th>Account</th>
        <th>Customer</th>
        <th>Amount</th>
        <th>Type</th>
        <th>Status</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
      @foreach($rows as $r)
      <tr>
        <td>{{ $r['id'] }}</td>
        <td>{{ $r['account'] }}</td>
        <td>{{ $r['customer'] }}</td>
        <td>{{ $r['amount'] }}</td>
        <td>{{ $r['type'] }}</td>
        <td>{{ $r['status'] }}</td>
        <td>{{ $r['date'] }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

</body>

</html>