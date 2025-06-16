<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;

class Client extends Model
{

    use Notifiable;

    protected $table = 'osis_client';
    protected $fillable = [
        'id',
        'superclient_id',
        'name',
        'domain',
        'referral_id',
        'apikey',
        'username',
        'password',
        'salt',
        'webhooks_enabled',
        'distributor_id',
        'll_customer_id',
        'll_api_policy',
        'll_key',
        'category',
        'start_date',
        'parcel_limit',
        'email_timeout',
        'has_ftp',
        'website',
        'estimated_start_date',
        'is_test_account',
        'status',
        'created',
        'updated'
    ];

    public function superclient()
    {
        return $this->belongsTo(Superclient::class, 'superclient_id', 'id');
    }
    public function getAllRecords($user)
    {
        $query = DB::table($this->table)->orderBy('name', 'asc');
        if (!empty($user->level) && $user->level === "Guest Admin" && !empty($user->id) && $user->id > 0) {
            $query->whereIn('id', function ($subQuery) use ($user) {
                $subQuery->select('client_id')
                    ->from('osis_admin_client')
                    ->where('admin_id', $user->id);
            });
        }
        return $query->get();
    }


    public function client_model_save(&$data)
    {
        $fields = [
            'account_type',
            'account_id',
            'contact_type',
            'is_customer_service',
            'name',
            'company',
            'email',
            'phone',
            'address1',
            'address2',
            'city',
            'state',
            'zip',
            'country',
            'website',
            'created',
            'updated'
        ];

        $insert_vals = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $fields)) {
                $insert_vals[$key] = $value;
            }
        }

        $client_id = DB::table('osis_client')->insertGetId($insert_vals);

        $data['client_id'] = $client_id;
        $this->save_extra($data);

        return $client_id;
    }

    public function get_policy_file($client_id)
    {
        $results = DB::table('osis_client_policy_file')
            ->where('client_id', '=', $client_id)
            ->first();

        if (!empty($results->filename)) {
            $results->file_contents = file_get_contents(public_path('policies/' . $results->filename . '.' . $results->file_type));
        }

        return (array) $results;
    }


    public function client_update($id, $data)
    {
        $record = self::find($id);

        if (!$record) {
            return null;
        }

        foreach ($data as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $record->$key = $value;
            }
        }

        $record->save();

        return $record;
    }

    public function generate_dist_aff_creds($client_id, $subclient_id, $username, $password)
    {
        $existingUser = DB::table('osis_old_api_user')
            ->where('username', $username)
            ->exists();

        if ($existingUser) {
            return 0;
        }

        $client = DB::table('osis_client')
            ->where('id', $client_id)
            ->first();

        if ($client && !empty($client->distributor_id)) {
            $distributor_id = $client->distributor_id;
        } else {
            $max_dist_sub = DB::table('osis_subclient')->max('distributor_id');
            $max_dist_cli = DB::table('osis_client')->max('distributor_id');
            $max_dist_old = DB::table('osis_old_api_user')->max('distributor_id');

            if ($max_dist_cli >= $max_dist_sub) {
                $distributor_id = $max_dist_cli > $max_dist_old ? $max_dist_cli + 1 : $max_dist_cli + 1;
            } else {
                $distributor_id = $max_dist_sub > $max_dist_old ? $max_dist_sub + 1 : $max_dist_sub + 1;
            }

            DB::table('osis_client')
                ->where('id', $client_id)
                ->update(['distributor_id' => $distributor_id]);
        }

        $subclient = DB::table('osis_subclient')
            ->where('id', $subclient_id)
            ->first();

        if (empty($subclient->affiliate_id)) {
            $max_aff = DB::table('osis_subclient')->max('affiliate_id');
            $affiliate_id = $max_aff + 1;

            DB::table('osis_subclient')
                ->where('id', $subclient_id)
                ->update(['affiliate_id' => $affiliate_id]);
        }

        $password_hash = Hash::make($password);

        DB::table('osis_old_api_user')->insert([
            'client_id' => $client_id,
            'distributor_id' => $distributor_id,
            'username' => $username,
            'password' => $password_hash
        ]);

        return 1;
    }


    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function api_key_exists($api_key)
    {
        $exists = DB::table('osis_client')
            ->where('apikey', $api_key)
            ->exists();

        return $exists ? 1 : 0;
    }

    public function save_policy_file($clientId, &$data)
    {
        $exists = DB::table('osis_client_policy_file')->where('client_id', $clientId)->exists();

        $data['file_contents'] = $data['file_type'] === 'txt'
            ? $data['txt_file_contents']
            : $data['html_file_contents'];

        if ($exists) {
            $policyFile = DB::table('osis_client_policy_file')
                ->where('client_id', $clientId)
                ->first();
            $data['filename'] = $policyFile->filename;
            DB::table('osis_client_policy_file')
                ->where('client_id', $clientId)
                ->update([
                    'file_type' => $data['file_type'],
                    'updated_at' => now(),
                ]);
        } else {
            $data['filename'] = $this->generateFilename($clientId);
            DB::table('osis_client_policy_file')->insert([
                'client_id' => $clientId,
                'filename' => $data['filename'],
                'file_type' => $data['file_type'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $filePath = __DIR__ . '/../../../web/policies/' . $data['filename'] . '.' . $data['file_type'];
        file_put_contents($filePath, $data['file_contents']);
    }

    public function subclients()
    {
        return $this->hasMany(Subclient::class);
    }
    public function contacts()
    {
        return $this->hasMany(Contact::class, 'account_id')
            ->where('account_type', 'client');
    }

    public function notes()
    {
        return $this->hasMany(Note::class, 'parent_id')->where('parent_type', 'client');
    }

    public function logins()
    {
        return $this->hasMany(ClientLogin::class);
    }
    public function permissions()
    {
        return $this->hasManyThrough(ClientLoginPermission::class, ClientLogin::class, 'client_id', 'client_login_id');
    }
}
