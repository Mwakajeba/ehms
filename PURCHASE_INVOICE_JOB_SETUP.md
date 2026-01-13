# Purchase Invoice Job Setup

## Overview

The purchase invoice system now uses a **queue job** to process items when there are **50 or more items** in an invoice. This solves the issue of items not being saved when there are many lines due to PHP's `max_input_vars` limit and execution timeouts.

## How It Works

### Automatic Detection
- **< 50 items**: Processed synchronously (immediate, same as before)
- **≥ 50 items**: Processed asynchronously via queue job

### Job Processing
1. Invoice record is created/updated immediately
2. Job is dispatched to process all items in batches
3. Job handles:
   - Creating all invoice items
   - Calculating totals
   - Posting GL transactions
   - Posting inventory movements
   - Updating linked assets

## Setup Instructions

### 1. Ensure Queue Worker is Running

The queue worker must be running to process jobs. Run this command:

```bash
php artisan queue:work --tries=3 --timeout=300
```

### 2. For Production (Using Supervisor)

Create a supervisor config file at `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/smartaccounting/artisan queue:work database --sleep=3 --tries=3 --max-time=3600 --timeout=300
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/html/smartaccounting/storage/logs/worker.log
stopwaitsecs=3600
```

Then:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

### 3. Verify Queue Connection

Check your `.env` file:
```env
QUEUE_CONNECTION=database
```

### 4. Run Migrations (if not already done)

The `jobs` table should already exist, but verify:
```bash
php artisan migrate
```

## Testing

1. Create a purchase invoice with **50+ items**
2. You should see a success message: *"Items are being processed in the background. Please refresh the page in a few moments."*
3. Check the invoice after a few seconds - all items should be there
4. Check logs: `storage/logs/laravel.log` for job processing messages

## Monitoring

### Check Queue Status
```bash
php artisan queue:work --once  # Process one job
php artisan queue:failed        # List failed jobs
php artisan queue:retry all     # Retry all failed jobs
```

### View Job Logs
```bash
tail -f storage/logs/laravel.log | grep ProcessPurchaseInvoiceItemsJob
```

## Troubleshooting

### Jobs Not Processing
1. **Check if queue worker is running:**
   ```bash
   ps aux | grep "queue:work"
   ```

2. **Check failed jobs:**
   ```bash
   php artisan queue:failed
   ```

3. **Restart queue worker:**
   ```bash
   php artisan queue:restart
   ```

### Items Still Not Saving
1. **Check PHP `max_input_vars` limit:**
   ```bash
   php -i | grep max_input_vars
   ```
   Should be at least 5000 for very large invoices.

2. **Check job logs** for errors:
   ```bash
   tail -f storage/logs/laravel.log
   ```

### Job Failing
- Check the `failed_jobs` table in database
- Review error logs in `storage/logs/laravel.log`
- Jobs will retry up to 3 times automatically

## Benefits

✅ **Handles large batches** (100+ items) without timeout  
✅ **Avoids `max_input_vars` issues** by processing in background  
✅ **Better error handling** with automatic retries  
✅ **Non-blocking** - user gets immediate response  
✅ **Scalable** - can process multiple invoices concurrently  

## Notes

- Invoices with < 50 items still process synchronously for immediate feedback
- Large invoices may take 10-30 seconds to fully process
- Users should refresh the invoice page after a few moments to see all items
- All GL transactions and inventory movements are handled by the job

