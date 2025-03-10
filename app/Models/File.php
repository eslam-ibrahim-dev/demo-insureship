<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Services\S3;

class File extends Model
{
    protected $table = 'osis_file'; 

    protected $fillable = [
        'parent_type', 'parent_id', 'file_type', 'filename', 'summary', 'admin_id', 'created', 'updated'
    ];

    protected $ftpUploadFields = [
        'id', 'client_id', 'filename', 'created'
    ];

    public static $fields_static = [
        'parent_type', 'parent_id', 'file_type', 'filename', 'summary', 'admin_id', 'created', 'updated'
    ];

    public static function get_by_id($id)
    {
        return self::find($id);
    }

    public static function get_ftp_upload_by_id($id)
    {
        return DB::table('osis_ftp_upload_file')->where('id', $id)->first();
    }

    public static function get_ftp_upload_by_client_id($client_id)
    {
        return DB::table('osis_ftp_upload_file')->where('client_id', $client_id)->get();
    }

    public static function get_by_parent($type, $id)
    {
        return self::join('osis_admin', 'osis_file.admin_id', '=', 'osis_admin.id')
            ->where('parent_type', $type)
            ->where('parent_id', $id)
            ->orderBy('osis_file.created', 'desc')
            ->get(['osis_file.*', 'osis_admin.name AS admin_name'])->toArray();
    }

    public static function save_file(&$data)
    {
        return self::create($data);
    }

    public static  function save_ftp_upload(&$data)
    {
        return DB::table('osis_ftp_upload_file')->insert($data);
    }

    public static function update_file(&$id, &$data)
    {
        $file = self::find($id);
        return $file->update($data);
    }

    public static function delete_file($id)
    {
        $file = self::find($id);
        $s3 = new S3(); 

        $s3->deleteFile($file->filename);

        return $file->delete();
    }

    public static function get_file_types()
    {
        return self::distinct()->get(['file_type'])->sortBy('file_type');
    }
}
