# InfinityFree deployment

InfinityFree serves `htdocs` as the web root, so upload the complete Laravel application there and keep the root `.htaccess` file. It routes requests into Laravel's `public` directory.

Before uploading:

1. Run `composer install --no-dev` locally and `npm run build`.
2. Do not upload `node_modules`, `.env`, `tests`, or local temporary files.
3. Create a MySQL database in the InfinityFree control panel and copy its host, database name, username, and password into `.env` on the server.
4. Copy the local `APP_KEY` into the server `.env` and set `APP_URL` to the live domain.
5. Import the exported database SQL file through phpMyAdmin.

InfinityFree does not provide SSH or Artisan access, so migrations, Composer, and frontend builds must be completed locally before upload.
