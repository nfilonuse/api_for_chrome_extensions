<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatentWork extends Model
{
    protected $table = false;
    private static $baseUrl='http://localhost:59595/api/pattent/';
    private static psProcessing = 0;
    private static psDone = 1;
    private static psError = 2;
    private static psNone = -1;

    /*
    *
    * $data.PattentNumber
    * $data.DownloadUrl
    */
    public ParsePattent($data)
    {
        return self::SendRequest('parsePattent',"POST", $data);
    }

    public getStatus($numberPattent)
    {
        $res=self::SendRequest('getstatus/'.$numberPattent,"GET");
        if ($res['code']==200)
        {
            return $res['data'];
        }
        return false;
    }

    /*
    *
    * $data.PattentNumber
    * $data.SearchString
    */
    public search($data)
    {
        return self::SendRequest('search', "POST", $data);
    }

    private static SendRequest($action,$method,$data=false)
    {
        
        $url=self::baseUrl.$action."/";
        $sendData='';
        if ($method=='GET'&&$data)
        {
            $sendData=http_build_query($data);
            $url.=$sendData;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($method=='POST')
        {
            $sendData=http_build_query($data);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,$sendData);
        }
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return [
            'code'=>$httpcode;
            'data'=>$response;
        ]

    }
}
