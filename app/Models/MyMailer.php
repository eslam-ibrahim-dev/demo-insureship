<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MyMailer extends Model
{
    // TODO: De-dupe these arrays
    // Used by Claims claims
    public static function getFullMailerByDomain()
    {
        $url = $_SERVER['SERVER_NAME'];
        $url = explode('.', $url);
        $domain = strtolower($url[1]);

        if (stristr($domain, "insureship") || stristr($domain, "osis")) { // InsureShip
            $mymailer['mailer'] = "swiftmailer.mailer.is_mailer";
            $mymailer['template'] = "insureship";
            $mymailer['company_name'] = "InsureShip";
            $mymailer['email'] = "no_reply@insureship.com";
            $mymailer['client_id'] = 56867;
            $mymailer['superclient_id'] = 1;
            $mymailer['claims_url'] = "https://claims.insureship.com";
            $mymailer['claims_phone'] = "866-701-3654";
            $mymailer['claims_email'] = "claims@insureship.com";
            $mymailer['api_url'] = "https://api.insureship.com";
            $mymailer['main_url'] = "https://www.insureship.com";
        } elseif (stristr($domain, "ticketguardian")) { // TicketGuardian
            $mymailer['mailer'] = "swiftmailer.mailer.tg_mailer";
            $mymailer['template'] = "ticketguardian";
            $mymailer['company_name'] = "TicketGuardian";
            $mymailer['email'] = "no_reply@ticketguardian.net";
            $mymailer['client_id'] = 56858;
            $mymailer['superclient_id'] = 7;
            $mymailer['claims_url'] = "https://claims.ticketguardian.net";
            $mymailer['claims_phone'] = "866-675-4673";
            $mymailer['claims_email'] = "claims@ticketguardian.net";
            $mymailer['api_url'] = "https://api.ticketguardian.net";
            $mymailer['main_url'] = "https://www.ticketguardian.net";
        } elseif (stristr($domain, "paycertify")) { // PayCertify
            $mymailer['mailer'] = "swiftmailer.mailer.pc_mailer";
            $mymailer['template'] = "paycertify";
            $mymailer['company_name'] = "PayCertify";
            $mymailer['email'] = "no_reply@paycertify.com";
            $mymailer['client_id'] = 56856;
            $mymailer['superclient_id'] = 3;
            $mymailer['claims_url'] = "https://claims.paycertify.com";
            $mymailer['claims_phone'] = "866-584-7008";
            $mymailer['claims_email'] = "claims@paycertify.com";
            $mymailer['api_url'] = "https://api.paycertify.com";
            $mymailer['main_url'] = "https://www.paycertify.com";
        } elseif (stristr($domain, "cycoverpro")) { // CyCoverPro
            $mymailer['mailer'] = "swiftmailer.mailer.ccp_mailer";
            $mymailer['template'] = "cycoverpro";
            $mymailer['company_name'] = "CyCoverPro";
            $mymailer['email'] = "no_reply@cycoverpro.com";
            $mymailer['client_id'] = 56860;
            $mymailer['superclient_id'] = 1;
            $mymailer['claims_url'] = "https://claims.cycoverpro.com";
            $mymailer['claims_phone'] = "866-258-4667";
            $mymailer['claims_email'] = "claims@cycoverpro.com";
            $mymailer['api_url'] = "https://api.cycoverpro.com";
            $mymailer['main_url'] = "https://www.cycoverpro.com";
        } elseif (stristr($domain, "pinpointintel")) { // PinpointIntel
            $mymailer['mailer'] = "swiftmailer.mailer.ppi_mailer";
            $mymailer['template'] = "pinpointintel";
            $mymailer['company_name'] = "PinpointIntel";
            $mymailer['email'] = "no_reply@pinpointintel.com";
            $mymailer['client_id'] = 56862;
            $mymailer['superclient_id'] = 4;
            $mymailer['claims_url'] = "https://claims.pinpointintel.com";
            $mymailer['claims_phone'] = "855-270-8452";
            $mymailer['claims_email'] = "claims@pinpointintel.com";
            $mymailer['api_url'] = "https://api.pinpointintel.com";
            $mymailer['main_url'] = "https://www.pinpointintel.com";
        } elseif (stristr($domain, "fulfilrr")) { // fulfilrr
            $mymailer['mailer'] = "swiftmailer.mailer.flr_mailer";
            $mymailer['template'] = "fulfilrr";
            $mymailer['company_name'] = "fulfilrr";
            $mymailer['email'] = "no_reply@fulfilrr.com";
            $mymailer['client_id'] = 57294;
            $mymailer['superclient_id'] = 4;
            $mymailer['claims_url'] = "https://claims.fulfilrr.com";
            $mymailer['claims_phone'] = "855-270-8452";
            $mymailer['claims_email'] = "claims@fulfilrr.com";
            $mymailer['api_url'] = "https://api.fulfilrr.com";
            $mymailer['main_url'] = "https://www.fulfilrr.com";
        } elseif (stristr($domain, "shopguaranteeit")) { // ShopGuaranteeIt
            $mymailer['mailer'] = "swiftmailer.mailer.sg_mailer";
            $mymailer['template'] = "shopguaranteeit";
            $mymailer['company_name'] = "ShopGuaranteeIt";
            $mymailer['email'] = "no_reply@shopguaranteeit.com";
            $mymailer['client_id'] = 56864;
            $mymailer['superclient_id'] = 6;
            $mymailer['claims_url'] = "https://claims.shopguaranteeit.com";
            $mymailer['claims_phone'] = "";
            $mymailer['claims_email'] = "claims@shopguaranteeit.com";
            $mymailer['api_url'] = "https://api.shopguaranteeit.com";
            $mymailer['main_url'] = "https://www.shopguaranteeit.com";
        } elseif ((stristr($domain, "shopguarantee") && !stristr($domain, "shopguaranteeit"))) { // ShopGuaranteeIt
            $mymailer['mailer'] = "swiftmailer.mailer.sg_mailer";
            $mymailer['template'] = "shopguarantee";
            $mymailer['company_name'] = "ShopGuarantee";
            $mymailer['email'] = "no_reply@shopguarantee.com";
            $mymailer['client_id'] = 56866; // ShipGuarantee, unfortunately
            $mymailer['superclient_id'] = 2;
            $mymailer['claims_url'] = "https://claims.shopguarantee.com";
            $mymailer['claims_phone'] = "866-675-4656";
            $mymailer['claims_email'] = "claims@shopguarantee.com";
            $mymailer['api_url'] = "https://api.shopguarantee.com";
            $mymailer['main_url'] = "https://www.shopguarantee.com";
        } else { // default
            $mymailer['mailer'] = "swiftmailer.mailer.is_mailer";
            $mymailer['template'] = "insureship";
            $mymailer['company_name'] = "InsureShip";
            $mymailer['email'] = "no_reply@insureship.com";
            $mymailer['client_id'] = 56867;
            $mymailer['superclient_id'] = 1;
            $mymailer['claims_url'] = "https://claims.insureship.com";
            $mymailer['claims_phone'] = "866-701-3654";
            $mymailer['claims_email'] = "claims@insureship.com";
            $mymailer['api_url'] = "https://api.insureship.com";
            $mymailer['main_url'] = "https://www.insureship.com";
        }

        return $mymailer;
    }

    // Used in Admin claims - not anymore - now using getMailerBySuperclientClientSubclientID
    public static function getMailerByClientID($id)
    {

        switch ($id) {
            case 56856: // PayCertify
                $mymailer['mailer'] = "swiftmailer.mailer.pc_mailer";
                $mymailer['template'] = "paycertify";
                $mymailer['company_name'] = "PayCertify";
                $mymailer['email'] = "no_reply@paycertify.com";
                $mymailer['client_id'] = 56856;
                $mymailer['claims_url'] = "https://claims.paycertify.com";
                $mymailer['claims_phone'] = "866-584-7008";
                $mymailer['claims_email'] = "claims@paycertify.com";
                $mymailer['api_url'] = "https://api.paycertify.com";
                $mymailer['main_url'] = "https://www.paycertify.com";
                break; // PayCertify
            case 56858: // TicketGuardian
                $mymailer['mailer'] = "swiftmailer.mailer.tg_mailer";
                $mymailer['template'] = "ticketguardian";
                $mymailer['company_name'] = "TicketGuardian";
                $mymailer['email'] = "no_reply@ticketguardian.net";
                $mymailer['client_id'] = 56858;
                $mymailer['claims_url'] = "https://claims.ticketguardian.net";
                $mymailer['claims_phone'] = "866-675-4673";
                $mymailer['claims_email'] = "claims@ticketguardian.net";
                $mymailer['api_url'] = "https://api.ticketguardian.net";
                $mymailer['main_url'] = "https://www.ticketguardian.net";
                break; // TicketGuardian
            case 56860: // CyCoverPro
                $mymailer['mailer'] = "swiftmailer.mailer.ccp_mailer";
                $mymailer['template'] = "cycoverpro";
                $mymailer['company_name'] = "CyCoverPro";
                $mymailer['email'] = "no_reply@cycoverpro.com";
                $mymailer['client_id'] = 56860;
                $mymailer['claims_url'] = "https://claims.cycoverpro.com";
                $mymailer['claims_phone'] = "866-258-4667";
                $mymailer['claims_email'] = "claims@cycoverpro.com";
                $mymailer['api_url'] = "https://api.cycoverpro.com";
                $mymailer['main_url'] = "https://www.cycoverpro.com";
                break; // CyCoverPro
            case 56862: // PinpointIntel
                $mymailer['mailer'] = "swiftmailer.mailer.ppi_mailer";
                $mymailer['template'] = "pinpointintel";
                $mymailer['company_name'] = "Pinpoint Intelligence";
                $mymailer['email'] = "no_reply@pinpointintel.com";
                $mymailer['client_id'] = 56862;
                $mymailer['claims_url'] = "https://claims.pinpointintel.com";
                $mymailer['claims_phone'] = "855-270-8452";
                $mymailer['claims_email'] = "claims@pinpointintel.com";
                $mymailer['api_url'] = "https://api.pinpointintel.com";
                $mymailer['main_url'] = "https://www.pinpointintel.com";
                break; // PinpointIntel
            case 57294: // fulfilrr
                $mymailer['mailer'] = "swiftmailer.mailer.flr_mailer";
                $mymailer['template'] = "fulfilrr";
                $mymailer['company_name'] = "fulfillr";
                $mymailer['email'] = "no_reply@fulfilrr.com";
                $mymailer['client_id'] = 57294;
                $mymailer['claims_url'] = "https://claims.fulfilrr.com";
                $mymailer['claims_phone'] = "855-270-8452";
                $mymailer['claims_email'] = "claims@fulfilrr.com";
                $mymailer['api_url'] = "https://api.fulfilrr.com";
                $mymailer['main_url'] = "https://www.fulfilrr.com";
                break; // fulfilrr
            case 56855: // ShipGuarantee
                $mymailer['mailer'] = "swiftmailer.mailer.sg_mailer";
                $mymailer['template'] = "shopguarantee";
                $mymailer['company_name'] = "ShopGuarantee";
                $mymailer['email'] = "no_reply@shopguarantee.com";
                $mymailer['client_id'] = 56855;
                $mymailer['claims_url'] = "https://claims.shopguarantee.com";
                $mymailer['claims_phone'] = "866-675-4656";
                $mymailer['claims_email'] = "claims@shopguarantee.com";
                $mymailer['api_url'] = "https://api.shopguarantee.com";
                $mymailer['main_url'] = "https://www.shopguarantee.com";
                break; // ShipGuarantee
            case 56854: // CyberGuarantee
                $mymailer['mailer'] = "swiftmailer.mailer.sg_mailer";
                $mymailer['template'] = "shopguarantee";
                $mymailer['company_name'] = "ShopGuarantee";
                $mymailer['email'] = "no_reply@shopguarantee.com";
                $mymailer['client_id'] = 56854;
                $mymailer['claims_url'] = "https://claims.shopguarantee.com";
                $mymailer['claims_phone'] = "866-439-0260";
                $mymailer['claims_email'] = "claims@shopguarantee.com";
                $mymailer['api_url'] = "https://api.shopguarantee.com";
                $mymailer['main_url'] = "https://www.shopguarantee.com";
                break; // CyberGuarantee
            case 56863: // FreshGuarantee
                $mymailer['mailer'] = "swiftmailer.mailer.sg_mailer";
                $mymailer['template'] = "shopguarantee";
                $mymailer['company_name'] = "ShopGuarantee";
                $mymailer['email'] = "no_reply@shopguarantee.com";
                $mymailer['client_id'] = 56863;
                $mymailer['claims_url'] = "https://claims.shopguarantee.com";
                $mymailer['claims_phone'] = "888-989-7721";
                $mymailer['claims_email'] = "claims@shopguarantee.com";
                $mymailer['api_url'] = "https://api.shopguarantee.com";
                $mymailer['main_url'] = "https://www.shopguarantee.com";
                break; // FreshGuarantee
            case 56864: // ShopGuaranteeIt
                $mymailer['mailer'] = "swiftmailer.mailer.sg_mailer";
                $mymailer['template'] = "shopguaranteeit";
                $mymailer['company_name'] = "ShopGuaranteeIt";
                $mymailer['email'] = "no_reply@shopguaranteeit.com";
                $mymailer['client_id'] = 56864;
                $mymailer['claims_url'] = "https://claims.shopguaranteeit.com";
                $mymailer['claims_phone'] = "";
                $mymailer['claims_email'] = "claims@shopguaranteeit.com";
                $mymailer['api_url'] = "https://api.shopguaranteeit.com";
                $mymailer['main_url'] = "https://www.shopguaranteeit.com";
                break; // ShopGuaranteeIt
            default: // ShopGuarantee and everything else
                $mymailer['mailer'] = "swiftmailer.mailer.sg_mailer";
                $mymailer['template'] = "shopguarantee";
                $mymailer['company_name'] = "ShopGuarantee";
                $mymailer['email'] = "no_reply@shopguarantee.com";
                $mymailer['client_id'] = 56866; // ShipGuarantee by default
                $mymailer['claims_url'] = "https://claims.shopguarantee.com";
                $mymailer['claims_phone'] = "866-675-4656";
                $mymailer['claims_email'] = "claims@shopguarantee.com";
                $mymailer['api_url'] = "https://api.shopguarantee.com";
                $mymailer['main_url'] = "https://www.shopguarantee.com";
        }

        return $mymailer;
    }

    // Used in policies - not anymore - now using getMailerBySuperclientClientSubclientID
    public static function getMailerByClientSubclientID($client_id, $subclient_id = 0)
    {
        if ($client_id == 56856) { // PayCertify
            $mymailer['mailer'] = "swiftmailer.mailer.pc_mailer";
            $mymailer['template'] = "paycertify";
            $mymailer['company_name'] = "PayCertify";
            $mymailer['email'] = "no_reply@paycertify.com";
            $mymailer['client_id'] = 56856;
            $mymailer['claims_url'] = "https://claims.paycertify.com";
            $mymailer['claims_phone'] = "866-584-7008";
            $mymailer['claims_email'] = "claims@paycertify.com";
            $mymailer['api_url'] = "https://api.paycertify.com";
            $mymailer['main_url'] = "https://www.paycertify.com";
        } elseif ($client_id == 56858) { // TicketGuardian
            $mymailer['mailer'] = "swiftmailer.mailer.tg_mailer";
            $mymailer['template'] = "ticketguardian";
            $mymailer['company_name'] = "TicketGuardian";
            $mymailer['email'] = "no_reply@ticketguardian.net";
            $mymailer['client_id'] = 56858;
            $mymailer['claims_url'] = "https://claims.ticketguardian.net";
            $mymailer['claims_phone'] = "866-675-4673";
            $mymailer['claims_email'] = "claims@ticketguardian.net";
            $mymailer['api_url'] = "https://api.ticketguardian.net";
            $mymailer['main_url'] = "https://www.ticketguardian.net";
        } elseif ($client_id == 56860) { // CyCoverPro
            $mymailer['mailer'] = "swiftmailer.mailer.ccp_mailer";
            $mymailer['template'] = "cycoverpro";
            $mymailer['company_name'] = "CyCoverPro";
            $mymailer['email'] = "no_reply@cycoverpro.com";
            $mymailer['client_id'] = 56860;
            $mymailer['claims_url'] = "https://claims.cycoverpro.com";
            $mymailer['claims_phone'] = "866-258-4667";
            $mymailer['claims_email'] = "claims@cycoverpro.com";
            $mymailer['api_url'] = "https://api.cycoverpro.com";
            $mymailer['main_url'] = "https://www.cycoverpro.com";
        } elseif ($client_id == 56862) { // PinpointIntel
            $mymailer['mailer'] = "swiftmailer.mailer.ppi_mailer";
            $mymailer['template'] = "pinpointintel";
            $mymailer['company_name'] = "Pinpoint Intelligence";
            $mymailer['email'] = "no_reply@pinpointintel.com";
            $mymailer['client_id'] = 56862;
            $mymailer['claims_url'] = "https://claims.pinpointintel.com";
            $mymailer['claims_phone'] = "855-270-8452";
            $mymailer['claims_email'] = "claims@pinpointintel.com";
            $mymailer['api_url'] = "https://api.pinpointintel.com";
            $mymailer['main_url'] = "https://www.pinpointintel.com";
        } elseif ($client_id == 57294) { // fulfilrr
            $mymailer['mailer'] = "swiftmailer.mailer.flr_mailer";
            $mymailer['template'] = "fulfilrr";
            $mymailer['company_name'] = "fulfillr";
            $mymailer['email'] = "no_reply@fulfilrr.com";
            $mymailer['client_id'] = 57294;
            $mymailer['claims_url'] = "https://claims.fulfilrr.com";
            $mymailer['claims_phone'] = "855-270-8452";
            $mymailer['claims_email'] = "claims@fulfilrr.com";
            $mymailer['api_url'] = "https://api.fulfilrr.com";
            $mymailer['main_url'] = "https://www.fulfilrr.com";
        } elseif ($client_id == 56863 || $subclient_id == 56896 || $subclient_id == 76919 || $subclient_id == 76924 || $subclient_id == 76918 || $subclient_id == 56876 || $subclient_id == 76923) { // FreshGuarantee
            $mymailer['mailer'] = "swiftmailer.mailer.sg_mailer";
            $mymailer['template'] = "freshguarantee";
            $mymailer['company_name'] = "FreshGuarantee";
            $mymailer['email'] = "no_reply@shopguarantee.com";
            $mymailer['client_id'] = 56863;
            $mymailer['claims_url'] = "https://claims.shopguarantee.com";
            $mymailer['claims_phone'] = "888-989-7721";
            $mymailer['claims_email'] = "claims@shopguarantee.com";
            $mymailer['api_url'] = "https://api.shopguarantee.com";
            $mymailer['main_url'] = "https://www.shopguarantee.com";
        } elseif ($client_id == 56855) { // ShipGuarantee
            $mymailer['mailer'] = "swiftmailer.mailer.sg_mailer";
            $mymailer['template'] = "shipguarantee";
            $mymailer['company_name'] = "ShipGuarantee";
            $mymailer['email'] = "no_reply@shopguarantee.com";
            $mymailer['client_id'] = 56855;
            $mymailer['claims_url'] = "https://claims.shopguarantee.com";
            $mymailer['claims_phone'] = "866-675-4656";
            $mymailer['claims_email'] = "claims@shopguarantee.com";
            $mymailer['api_url'] = "https://api.shopguarantee.com";
            $mymailer['main_url'] = "https://www.shopguarantee.com";
        } elseif ($client_id == 56854) { // CyberGuarantee
            $mymailer['mailer'] = "swiftmailer.mailer.sg_mailer";
            $mymailer['template'] = "cyberguarantee";
            $mymailer['company_name'] = "CyberGuarantee";
            $mymailer['email'] = "no_reply@shopguarantee.com";
            $mymailer['client_id'] = 56854;
            $mymailer['claims_url'] = "https://claims.shopguarantee.com";
            $mymailer['claims_phone'] = "866-439-0260";
            $mymailer['claims_email'] = "claims@shopguarantee.com";
            $mymailer['api_url'] = "https://api.shopguarantee.com";
            $mymailer['main_url'] = "https://www.shopguarantee.com";
        } elseif ($client_id == 56864) { // ShopGuaranteeIt
            $mymailer['mailer'] = "swiftmailer.mailer.sg_mailer";
            $mymailer['template'] = "shopguaranteeit";
            $mymailer['company_name'] = "ShopGuaranteeIt";
            $mymailer['email'] = "no_reply@shopguaranteeit.com";
            $mymailer['client_id'] = 56864;
            $mymailer['claims_url'] = "https://claims.shopguaranteeit.com";
            $mymailer['claims_phone'] = "";
            $mymailer['claims_email'] = "claims@shopguaranteeit.com";
            $mymailer['api_url'] = "https://api.shopguaranteeit.com";
            $mymailer['main_url'] = "https://www.shopguaranteeit.com";
        } else { // ShopGuarantee and everything else
            $mymailer['mailer'] = "swiftmailer.mailer.sg_mailer";
            $mymailer['template'] = "shopguarantee";
            $mymailer['company_name'] = "ShopGuarantee";
            $mymailer['email'] = "no_reply@shopguarantee.com";
            $mymailer['client_id'] = 56866; // ShipGuarantee by default
            $mymailer['claims_url'] = "https://claims.shopguarantee.com";
            $mymailer['claims_phone'] = "888-989-7720";
            $mymailer['claims_email'] = "claims@shopguarantee.com";
            $mymailer['api_url'] = "https://api.shopguarantee.com";
            $mymailer['main_url'] = "https://www.shopguarantee.com";
        } // ShopGuarantee and everything else

        return $mymailer;
    }

    // Not used
    public static function getMailerBySuperclientID($id)
    {
        //
    }

    // all are using this one for now, until we can move it to the database - Claims claims, Admin claims, and policies
    public static function getMailerBySuperclientClientSubclientID($client_id, $subclient_id = 0, $superclient_id = 0)
    {
        if ($superclient_id == 1) { // InsureShip
            $mymailer['mailer'] = "swiftmailer.mailer.is_mailer";
            $mymailer['template'] = "insureship";
            $mymailer['company_name'] = "InsureShip";
            $mymailer['email'] = "no_reply@insureship.com";
            $mymailer['client_id'] = 56867;
            $mymailer['claims_url'] = "https://claims.insureship.com";
            $mymailer['claims_phone'] = "866-701-3654";
            $mymailer['claims_email'] = "claims@insureship.com";
            $mymailer['api_url'] = "https://api.insureship.com";
            $mymailer['main_url'] = "https://www.insureship.com";
        } elseif ($client_id == 1) { // Test Client
            $mymailer['mailer'] = "swiftmailer.mailer.sg_mailer";
            $mymailer['template'] = "testclient";
            $mymailer['company_name'] = "ShopGuarantee";
            $mymailer['email'] = "no_reply@shopguarantee.com";
            $mymailer['client_id'] = 1;
            $mymailer['claims_url'] = "https://claims.shopguarantee.com";
            $mymailer['claims_phone'] = "888-989-7720";
            $mymailer['claims_email'] = "claims@shopguarantee.com";
            $mymailer['api_url'] = "https://api.shopguarantee.com";
            $mymailer['main_url'] = "https://www.shopguarantee.com";
        } elseif ($client_id == 56856) { // PayCertify
            $mymailer['mailer'] = "swiftmailer.mailer.pc_mailer";
            $mymailer['template'] = "paycertify";
            $mymailer['company_name'] = "PayCertify";
            $mymailer['email'] = "no_reply@paycertify.com";
            $mymailer['client_id'] = 56856;
            $mymailer['claims_url'] = "https://claims.paycertify.com";
            $mymailer['claims_phone'] = "866-584-7008";
            $mymailer['claims_email'] = "claims@paycertify.com";
            $mymailer['api_url'] = "https://api.paycertify.com";
            $mymailer['main_url'] = "https://www.paycertify.com";
        } elseif ($client_id == 56858) { // TicketGuardian
            $mymailer['mailer'] = "swiftmailer.mailer.tg_mailer";
            $mymailer['template'] = "ticketguardian";
            $mymailer['company_name'] = "TicketGuardian";
            $mymailer['email'] = "no_reply@ticketguardian.net";
            $mymailer['client_id'] = 56858;
            $mymailer['claims_url'] = "https://claims.ticketguardian.net";
            $mymailer['claims_phone'] = "866-675-4673";
            $mymailer['claims_email'] = "claims@ticketguardian.net";
            $mymailer['api_url'] = "https://api.ticketguardian.net";
            $mymailer['main_url'] = "https://www.ticketguardian.net";
        } elseif ($client_id == 56860) { // CyCoverPro
            $mymailer['mailer'] = "swiftmailer.mailer.ccp_mailer";
            $mymailer['template'] = "cycoverpro";
            $mymailer['company_name'] = "CyCoverPro";
            $mymailer['email'] = "no_reply@cycoverpro.com";
            $mymailer['client_id'] = 56860;
            $mymailer['claims_url'] = "https://claims.cycoverpro.com";
            $mymailer['claims_phone'] = "866-258-4667";
            $mymailer['claims_email'] = "claims@cycoverpro.com";
            $mymailer['api_url'] = "https://api.cycoverpro.com";
            $mymailer['main_url'] = "https://www.cycoverpro.com";
        } elseif ($client_id == 56862) { // PinpointIntel
            $mymailer['mailer'] = "swiftmailer.mailer.ppi_mailer";
            $mymailer['template'] = "pinpointintel";
            $mymailer['company_name'] = "Pinpoint Intelligence";
            $mymailer['email'] = "no_reply@pinpointintel.com";
            $mymailer['client_id'] = 56862;
            $mymailer['claims_url'] = "https://claims.pinpointintel.com";
            $mymailer['claims_phone'] = "855-270-8452";
            $mymailer['claims_email'] = "claims@pinpointintel.com";
            $mymailer['api_url'] = "https://api.pinpointintel.com";
            $mymailer['main_url'] = "https://www.pinpointintel.com";
        } elseif ($client_id == 57294) { // fulfilrr
            $mymailer['mailer'] = "swiftmailer.mailer.flr_mailer";
            $mymailer['template'] = "fulfilrr";
            $mymailer['company_name'] = "fulfillr";
            $mymailer['email'] = "no_reply@fulfilrr.com";
            $mymailer['client_id'] = 57294;
            $mymailer['claims_url'] = "https://claims.fulfilrr.com";
            $mymailer['claims_phone'] = "855-270-8452";
            $mymailer['claims_email'] = "claims@fulfilrr.com";
            $mymailer['api_url'] = "https://api.fulfilrr.com";
            $mymailer['main_url'] = "https://www.fulfilrr.com";
        } elseif ($client_id == 56863 || $subclient_id == 56896 || $subclient_id == 76919 || $subclient_id == 76924 || $subclient_id == 76918 || $subclient_id == 56876 || $subclient_id == 76923) { // FreshGuarantee
            $mymailer['mailer'] = "swiftmailer.mailer.sg_mailer";
            $mymailer['template'] = "freshguarantee";
            $mymailer['company_name'] = "FreshGuarantee";
            $mymailer['email'] = "no_reply@shopguarantee.com";
            $mymailer['client_id'] = 56863;
            $mymailer['claims_url'] = "https://claims.shopguarantee.com";
            $mymailer['claims_phone'] = "888-989-7721";
            $mymailer['claims_email'] = "claims@shopguarantee.com";
            $mymailer['api_url'] = "https://api.shopguarantee.com";
            $mymailer['main_url'] = "https://www.shopguarantee.com";
        } elseif ($client_id == 56855) { // ShipGuarantee
            $mymailer['mailer'] = "swiftmailer.mailer.sg_mailer";
            $mymailer['template'] = "shipguarantee";
            $mymailer['company_name'] = "ShipGuarantee";
            $mymailer['email'] = "no_reply@shopguarantee.com";
            $mymailer['client_id'] = 56855;
            $mymailer['claims_url'] = "https://claims.shopguarantee.com";
            $mymailer['claims_phone'] = "866-675-4656";
            $mymailer['claims_email'] = "claims@shopguarantee.com";
            $mymailer['api_url'] = "https://api.shopguarantee.com";
            $mymailer['main_url'] = "https://www.shopguarantee.com";
        } elseif ($client_id == 56854) { // CyberGuarantee
            $mymailer['mailer'] = "swiftmailer.mailer.sg_mailer";
            $mymailer['template'] = "cyberguarantee";
            $mymailer['company_name'] = "CyberGuarantee";
            $mymailer['email'] = "no_reply@shopguarantee.com";
            $mymailer['client_id'] = 56854;
            $mymailer['claims_url'] = "https://claims.shopguarantee.com";
            $mymailer['claims_phone'] = "866-439-0260";
            $mymailer['claims_email'] = "claims@shopguarantee.com";
            $mymailer['api_url'] = "https://api.shopguarantee.com";
            $mymailer['main_url'] = "https://www.shopguarantee.com";
        } elseif ($client_id == 56864) { // ShopGuaranteeIt
            $mymailer['mailer'] = "swiftmailer.mailer.sg_mailer";
            $mymailer['template'] = "shopguaranteeit";
            $mymailer['company_name'] = "ShopGuaranteeIt";
            $mymailer['email'] = "no_reply@shopguaranteeit.com";
            $mymailer['client_id'] = 56864;
            $mymailer['claims_url'] = "https://claims.shopguaranteeit.com";
            $mymailer['claims_phone'] = "";
            $mymailer['claims_email'] = "claims@shopguaranteeit.com";
            $mymailer['api_url'] = "https://api.shopguaranteeit.com";
            $mymailer['main_url'] = "https://www.shopguaranteeit.com";
        } else { // ShopGuarantee and everything else
            $mymailer['mailer'] = "swiftmailer.mailer.sg_mailer";
            $mymailer['template'] = "shopguarantee";
            $mymailer['company_name'] = "ShopGuarantee";
            $mymailer['email'] = "no_reply@shopguarantee.com";
            $mymailer['client_id'] = 56866; // ShipGuarantee by default
            $mymailer['claims_url'] = "https://claims.shopguarantee.com";
            $mymailer['claims_phone'] = "888-989-7720";
            $mymailer['claims_email'] = "claims@shopguarantee.com";
            $mymailer['api_url'] = "https://api.shopguarantee.com";
            $mymailer['main_url'] = "https://www.shopguarantee.com";
        } // ShopGuarantee and everything else

        return $mymailer;
    }
}
