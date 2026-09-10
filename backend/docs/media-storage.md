# Media storage

Client logos and films are written to the disks named by `config('filesystems.logos')`
and `config('filesystems.films')`. Locally those are the `public` disk; in production it is Cloudflare R2.
No application code changes between the two.

## Switching to R2

Once IT provides the bucket and token, set these in `.env` on the server.
Never in `.env.example`, and never committed — the repository is public.

```dotenv
FILM_DISK=s3

AWS_ACCESS_KEY_ID=<from IT>
AWS_SECRET_ACCESS_KEY=<from IT>
AWS_DEFAULT_REGION=auto
AWS_BUCKET=<bucket name>
AWS_ENDPOINT=https://<account id>.r2.cloudflarestorage.com
AWS_URL=https://media.<our domain>
AWS_USE_PATH_STYLE_ENDPOINT=true

# Browser uploads straight to the bucket instead of through PHP.
LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=s3
```

Then `php artisan config:clear`.

`AWS_URL` matters: without it `Storage::url()` returns a signed link that
expires, so films would play for a few minutes and then start returning 403.
With it set, URLs are stable and public.

## Why uploads go direct to the bucket

With `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=s3`, Livewire hands the browser a
presigned URL and the file is `PUT` straight to R2. The PHP process never
receives the bytes, so `upload_max_filesize`, `post_max_size` and the request
timeout stop being limits. This is what makes multi-gigabyte films practical.

It requires the bucket CORS policy to allow `PUT` from our origins. Without
that the browser blocks the request and the upload fails silently.

## After the first real upload, check these

These could not be verified locally, because they need a live bucket:

1. **ACLs.** Livewire signs its presigned `putObject` with `ACL: private`, and
   R2 does not implement ACLs. If uploads fail with `NotImplemented`, that is
   the cause.
2. **Checksums.** The AWS SDK adds CRC32 checksums by default. `config/filesystems.php`
   already sets `request_checksum_calculation` to `when_required` to avoid this,
   but confirm it against the real endpoint.
3. **Public reads.** Confirm a film plays from `AWS_URL` in a logged-out browser.
   If it 403s, the bucket's custom domain binding is not public.

## Logos stay local, films go to R2

Logos and films use separate disks because they behave differently:

- **Logos** (`LOGO_DISK`, default `public`) are a few KB each, static, and
  committed under `storage/app/public/logos`. They ship with a deploy, so there
  is nothing to upload and no reason to pay for object storage.
- **Films** (`FILM_DISK`) are large and uploaded through the studio, so these
  are what R2 is for.

`portfolio:sync-logos` reads `config('filesystems.logos')` rather than a
hardcoded disk. It also refuses to run when a **remote** logo disk turns up
empty, because that almost always means the bucket or credentials are wrong,
and the command clears the logo path of every client it cannot find a file for
- which would wipe all 17 assignments. Pass `--force` to override. On a local
disk the old behaviour is unchanged: an empty folder really does mean the
logos were deleted.

## Temporary upload cleanup

Direct uploads land in `livewire-tmp/` before being moved into place.
Interrupted uploads leave files behind, so set a lifecycle rule once:

```sh
php artisan livewire:configure-s3-upload-cleanup
```

## Local development

Leave `LOGO_DISK` and `FILM_DISK` on `public`, and `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK` unset.
Files go to `storage/app/public`, served through the `public/storage` symlink
created by `php artisan storage:link`.
