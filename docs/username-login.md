# Staff login

`POST /api/v1/auth/login` accepts username and password:

```json
{
  "username": "admin",
  "password": "your-password"
}
```

Email is no longer accepted as the login identifier. Successful responses include
`data.token` and `data.user.username`. Send the token as `Authorization: Bearer <token>`.
Role permissions are unchanged.

Usernames must be unique, contain 1–50 lowercase letters, numbers, dots, underscores,
or hyphens. Creating staff through `/api/v1/admin/users` now requires a username;
updates can change it. Email remains required staff contact information.

Run `php artisan migrate` to add usernames to existing accounts. Existing passwords
are preserved. Usernames are derived from the part of the email before `@`, converted
to lowercase and stripped of unsupported characters. Collisions receive suffixes
such as `admin-1`. Seeded demo accounts use `admin`, `kitchen`, and `cashier`.

The frontend login form and request body must use `username` instead of `email`.
The frontend staff management form must also provide a username when creating users.
