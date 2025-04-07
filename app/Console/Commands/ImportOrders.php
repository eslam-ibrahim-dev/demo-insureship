<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = storage_path('app/import/sub_orders.csv');

        if (!file_exists($filePath)) {
            $this->error("File not found: $filePath");
            return 1;
        }

        $file = fopen($filePath, 'r');
        $headers = fgetcsv($file); // get column names from first line
        $headers = array_filter($headers); // Remove any empty headers
        $count = 0;

        while (($row = fgetcsv($file)) !== false) {
            $row = array_slice($row, 0, count($headers)); // trim extra values
            $data = array_combine($headers, $row);

            // Check if 'order_date' exists in the data and convert it
            if (isset($data['order_date']) && !empty($data['order_date'])) {
                try {
                    // Use 24-hour format (m/d/Y H:i) to handle the input like '3/12/2027 0:00'
                    $orderDate = Carbon::createFromFormat('m/d/Y H:i', $data['order_date']);

                    // Convert the date to the desired format (Y-m-d H:i:s)
                    $data['order_date'] = $orderDate->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    // Handle the exception if the date is invalid
                    $this->error("Invalid date format for order_date: " . $data['order_date']);
                    continue; // Skip this row if the date is invalid
                }
            }

            if (isset($data['ship_date']) && !empty($data['ship_date'])) {
                try {
                    // Use 24-hour format (m/d/Y H:i) for 'ship_date'
                    $shipDate = Carbon::createFromFormat('m/d/Y H:i', $data['ship_date']);
                    
                    // Convert the date to the desired format (Y-m-d H:i:s)
                    $data['ship_date'] = $shipDate->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    $this->error("Invalid date format for ship_date: " . $data['ship_date']);
                    continue; // Skip this row if the date is invalid
                }
            }
            if (isset($data['email_time']) && !empty($data['email_time'])) {
                try {
                    // Use 24-hour format (m/d/Y H:i) for 'email_time'
                    $email_time = Carbon::createFromFormat('m/d/Y H:i', $data['email_time']);
                    
                    // Convert the date to the desired format (Y-m-d H:i:s)
                    $data['email_time'] = $email_time->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    $this->error("Invalid date format for email_time: " . $data['email_time']);
                    continue; // Skip this row if the date is invalid
                }
            }
            if (isset($data['void_date']) && !empty($data['void_date'])) {
                try {
                    // Use 24-hour format (m/d/Y H:i) for 'void_date'
                    $void_date = Carbon::createFromFormat('m/d/Y H:i', $data['void_date']);
                    
                    // Convert the date to the desired format (Y-m-d H:i:s)
                    $data['void_date'] = $void_date->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    $this->error("Invalid date format for void_date: " . $data['void_date']);
                    continue; // Skip this row if the date is invalid
                }
            }
            if (isset($data['register_date']) && !empty($data['register_date'])) {
                try {
                    // Use 24-hour format (m/d/Y H:i) for 'register_date'
                    $register_date = Carbon::createFromFormat('m/d/Y H:i', $data['register_date']);
                    
                    // Convert the date to the desired format (Y-m-d H:i:s)
                    $data['register_date'] = $register_date->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    $this->error("Invalid date format for register_date: " . $data['register_date']);
                    continue; // Skip this row if the date is invalid
                }
            }
            if (isset($data['created']) && !empty($data['created'])) {
                try {
                    // Use 24-hour format (m/d/Y H:i) for 'created'
                    $created = Carbon::createFromFormat('m/d/Y H:i', $data['created']);
                    
                    // Convert the date to the desired format (Y-m-d H:i:s)
                    $data['created'] = $created->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    $this->error("Invalid date format for created: " . $data['created']);
                    continue; // Skip this row if the date is invalid
                }
            }
            if (isset($data['updated']) && !empty($data['updated'])) {
                try {
                    // Use 24-hour format (m/d/Y H:i) for 'updated'
                    $updated = Carbon::createFromFormat('m/d/Y H:i', $data['updated']);
                    
                    // Convert the date to the desired format (Y-m-d H:i:s)
                    $data['updated'] = $updated->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    $this->error("Invalid date format for updated: " . $data['updated']);
                    continue; // Skip this row if the date is invalid
                }
            }

            // Option 1: Use DB::table
            DB::table('osis_subclient')->insert($data);

            // Option 2: Use Eloquent model (if defined)
            // \App\Models\Client::create($data);

            $count++;
        }

        fclose($file);
        $this->info("Imported $count clients successfully.");
        return 0;
    }
}
