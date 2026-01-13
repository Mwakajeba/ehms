<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Hr\BiometricService;
use App\Models\Hr\BiometricDevice;

class SyncBiometricDevices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'biometric:sync {--device-id= : Sync specific device}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync data from biometric devices that need sync';

    protected $biometricService;

    public function __construct(BiometricService $biometricService)
    {
        parent::__construct();
        $this->biometricService = $biometricService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $deviceId = $this->option('device-id');

        if ($deviceId) {
            $device = BiometricDevice::find($deviceId);
            if (!$device) {
                $this->error("Device with ID {$deviceId} not found.");
                return Command::FAILURE;
            }

            if (!$device->needsSync()) {
                $this->info("Device {$device->device_name} does not need sync yet.");
                return Command::SUCCESS;
            }

            $this->info("Syncing device: {$device->device_name}");
            $result = $this->biometricService->syncDevice($device);
            
            if ($result['success']) {
                $this->info("✓ Sync successful");
            } else {
                $this->error("✗ Sync failed: {$result['message']}");
            }

            // Process pending logs after sync
            $processResult = $this->biometricService->processPendingLogs($device->id);
            $this->info("Processed {$processResult['processed']} logs");

        } else {
            // Sync all devices that need sync
            $devices = BiometricDevice::needsSync()->get();
            
            if ($devices->isEmpty()) {
                $this->info("No devices need sync at this time.");
                return Command::SUCCESS;
            }

            $this->info("Found {$devices->count()} device(s) that need sync");

            foreach ($devices as $device) {
                $this->info("Syncing device: {$device->device_name}");
                $result = $this->biometricService->syncDevice($device);
                
                if ($result['success']) {
                    $this->info("  ✓ Sync successful");
                    
                    // Process pending logs
                    $processResult = $this->biometricService->processPendingLogs($device->id);
                    $this->info("  Processed {$processResult['processed']} logs");
                } else {
                    $this->error("  ✗ Sync failed: {$result['message']}");
                }
            }
        }

        return Command::SUCCESS;
    }
}

