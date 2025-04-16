<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $table = "osis_offer";
    protected $fillable = [
        'id', 'name', 'link_name', 'terms', 'icon',
        'coverage_start', 'coverage_duration',
        'file_claim_start', 'file_claim_duration',
        'created', 'updated',
    ];
    public $fields = array(
        'id', 'name', 'link_name', 'terms', 'icon',
        'coverage_start', 'coverage_duration',
        'file_claim_start', 'file_claim_duration',
        'created', 'updated'
    );

    public static $fields_static = array(
        'id', 'name', 'link_name', 'terms', 'icon',
        'coverage_start', 'coverage_duration',
        'file_claim_start', 'file_claim_duration',
        'created', 'updated'
    );

    public function get_terms($offer_id)
    {
        $terms = DB::table('osis_offer')->where('id', $offer_id)->value('terms');

        return $terms;
    }
    public function get_terms_for_client($offer_id, $client_id)
    {
        $terms = DB::table('osis_client_offer')
                    ->where('offer_id', $offer_id)
                    ->where('client_id', $client_id)
                    ->value('terms'); 

        return $terms;
    }



    public function getOffersBySubclientId($subclient_id)
    {
        return DB::table('osis_offer as a')
            ->join('osis_client_offer as b', 'a.id', '=', 'b.offer_id')
            ->where('b.subclient_id', $subclient_id)
            ->select(
                'a.*',
                'b.id as subclient_offer_id',
                'b.terms as subclient_terms',
                'b.offer_id as main_offer_id'
            )
            ->get();
    }



    public function get_offer_id_by_claim_id($claim_id)
    {
        $results = DB::table('osis_order_offer')->where('claim_id', $claim_id)->count();
        if ($results > 0) {
            return DB::table('osis_order_offer')->where('claim_id', $claim_id)->value('offer_id');
        }
        return 0;
    }

    public function get_id_by_order_id_and_claim_type($order_id, $claim_type)
    {
        $result = DB::table('osis_order_offer as a')
            ->join('osis_offer as b', 'a.offer_id', '=', 'b.id')
            ->where('a.order_id', $order_id)
            ->where('b.link_name', $claim_type)
            ->select('a.id as id')
            ->first();

        return $result ? $result->id : null;
    }


    public function add_offer_to_client($offer_id, $client_id)
    {
        $terms = $this->get_terms($offer_id);

        DB::table('osis_client_offer')->insert([
            'client_id' => $client_id,
            'offer_id' => $offer_id,
            'terms' => $terms,
        ]);
    }


    public function add_offer_to_subclient($offer_id, $subclient_id, $client_id)
    {
        $terms = $this->get_terms_for_client($offer_id, $client_id);
        DB::table('osis_client_offer')->insert([
            'subclient_id' => $subclient_id,
            'offer_id' => $offer_id,
            'terms' => $terms
        ]);
    }


    public function add_offer_to_order($offer_id, $order_id, $subclient_id)
    {
        $terms = $this->get_terms_for_subclient($offer_id, $subclient_id);

        DB::table('osis_order_offer')->insert([
            'offer_id' => $offer_id,
            'order_id' => $order_id,
            'terms' => $terms,
        ]);
    }


}
