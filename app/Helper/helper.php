<?php

use Carbon\Carbon;
use App\Models\Holiday;
use App\Models\Utility;
use App\Models\ChangeLog;
use Illuminate\Support\Arr;
use GuzzleHttp\Psr7\Request;
use Illuminate\Http\Response;
use Doctrine\Common\Cache\Cache;
use Harimayco\Menu\Models\Menus;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\RedirectResponse;

if (!function_exists('look')) {
    function look($array, $print_r = 1, $exit = 1)
    {
        echo "<pre>";
        echo PHP_EOL . "=========================" . PHP_EOL;
        if ($print_r == 1) print_r($array);
        else var_dump($array);
        echo PHP_EOL . "=========================" . PHP_EOL;
        echo "</pre>";

        if ($exit)
            exit();
    }
}


if (!function_exists('ex_encrypt')) {
    function ex_encrypt($text, $urlEncode = NULL, $salt = NULL)
    {
        $salt = ($salt) ? $salt : env('ENCRYPT_SALT');
        $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext_raw = openssl_encrypt($text, $cipher, $salt, $options = OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $ciphertext_raw, $salt, $as_binary = true);
        $returnData =  base64_encode($iv . $hmac . $ciphertext_raw);
        if ($urlEncode) {
            $returnData = urlencode(utf8_encode($returnData));
        }

        return $returnData;
    }
}

if (!function_exists('ex_decrypt')) {
    function ex_decrypt($text, $salt = null)
    {
        $salt = ($salt) ? $salt : env('ENCRYPT_SALT');
        $c = base64_decode($text);
        $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
        $iv = substr($c, 0, $ivlen);
        $hmac = substr($c, $ivlen, $sha2len = 32);
        $text_raw = substr($c, $ivlen + $sha2len);
        $original_plaintext = @openssl_decrypt($text_raw, $cipher, $salt, $options = OPENSSL_RAW_DATA, $iv);
        $calcmac = hash_hmac('sha256', $text_raw, $salt, $as_binary = true);
        if (@hash_equals($hmac, $calcmac)) //PHP 5.6+ timing attack safe comparison
        {
            return $original_plaintext;
        }

        return $text;
    }
}

if (!function_exists('buildTree')) {
    function buildTree(array $elements, $level = 1, $parentId = 0)
    {
        $branch = NULL;
        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                $children = buildTree($elements, $level + 1, $element['id']);

                if ($children) {
                    $element['ul'] = '<ul>' . $children . '</ul>';
                }

                $branch .= '<li data-id=a' . $element['id'] . ' data-level=' . $level . '><span>' . $element['description'] . '</span>' . (isset($element['ul']) ? $element['ul'] : "") . '</li>';
            }
        }

        return $branch;
    }
}

if (!function_exists('assetz')) {
    function assetz($src, $version = "")
    {
        $version = (($version == "") ? '?v=' . env('PJVER') : $version);
        return asset($src . $version);
    }
}

if (!function_exists('imageName')) {
    function imageName($name, $withExt = 1, $prefix = NULL, $suffix = NULL)
    {
        $extension = pathinfo($name, PATHINFO_EXTENSION);
        $name = preg_replace("/[^a-zA-Z0-9]/", "_", pathinfo($name, PATHINFO_FILENAME));
        $name = $prefix . '_' . $name . '_' . $suffix;
        if ($prefix == NULL) {
            $name = substr($name, -170);
        } else {
            $name = substr($name, 0, 170);
        }

        $name .= ('_' . time());

        if ($withExt) {
            $name .= ('.' . $extension);
        }

        return $name;
    }
}

if (!function_exists('imageExtension')) {
    function imageExtension($name)
    {
        return pathinfo($name, PATHINFO_EXTENSION);
    }
}

if (!function_exists('licenseStatus')) {
    function licenseStatus($code)
    {
        if ($code == 0) return "Not Assigned";
        if ($code == 1) return "Active";
        if ($code == 2) return "Expired";
        if ($code == 3) return "Server Locked";
        if ($code == 4) return "Suspended";
        if ($code == 5) return "Renewed";
        if ($code == 6) return "Canceled";
        if ($code == 7) return "Inactive";
    }
}

if (!function_exists('approvalStatus')) {
    function approvalStatus($code)
    {
        if ($code == 1) return "New Approved";
        if ($code == 2) return "New Rejected";
        if ($code == 3) return "Change Approved";
        if ($code == 4) return "Change Rejected";
    }
}

if (!function_exists('appDate')) {
    function appDate($dateP, $time = false)
    {
        try {
            $date = new \DateTime($dateP);
        } catch (\Exception $e) {
            return $dateP;
        }
        return $date->format(env('APP_DATE_FORMAT', 'd/M/Y') . (($time) ? env('APP_TIME_FORMAT', ' H:i:s') : ''));
    }
}

/**
 * overWrite the Env File values.
 */
if (!function_exists('setEnv')) {
    function setEnv($type, $val, $forceAdd = false)
    {
        $path = app()->environmentFilePath();
        if (file_exists($path)) {
            $val = '"' . trim($val) . '"';
            if (strpos(file_get_contents($path), $type) != false && strpos(file_get_contents($path), $type) >= 0) {
                if ($forceAdd) {
                    file_put_contents($path, file_get_contents($path) . $type . '=' . $val . PHP_EOL);
                }
                file_put_contents($path, str_replace(
                    $type . '="' . env($type) . '"',
                    $type . '=' . $val,
                    file_get_contents($path)
                ));
            } else {
                file_put_contents($path, file_get_contents($path) . $type . '=' . $val . PHP_EOL);
            }
        }
    }
}

function replace_regx($input, $otherRegx = '', $allowTags = '')
{
    $regx = array(
        'amp'       => '/ & /',             //Amp
        'hdoc'      => '/"/',               //Heredoc
        'ndoc'      => "/\'/",              //Nowdoc
        'gt'        => '/>/',               //Greater than
        'lt'        => '/</',               //Less than
        'startPra'  => '/\(/',              //Opening parenthesis
        'endPra'    => '/\)/',              //Closing parenthesis
    );

    $replacement = array(
        'amp'       => ' &#38; ',           //Amp
        'hdoc'      => '&#34;',             //Heredoc
        'ndoc'      => '&#39;',             //Nowdoc
        'gt'        => '&#62;',             //Greater than
        'lt'        => '&#60;',             //Less than
        'startPra'  => '&#40;',             //Opening parenthesis
        'endPra'    => '&#41;',             //Closing parenthesis
    );

    if (is_array($allowTags)) {
        foreach ($allowTags as $valueAllow) {
            if (isset($regx[$valueAllow]))
                unset($regx[$valueAllow]);

            if (isset($replacement[$valueAllow]))
                unset($replacement[$valueAllow]);
        }
    }

    if (is_array($otherRegx)) {
        foreach ($otherRegx as $valueRegx) {
            $otherRegxSub = explode('^', $valueRegx);
            $regx[] = '/' . $otherRegxSub[0] . '/';
            $replacement[] = $otherRegxSub[1];
        }
    }

    if (!is_string($input) && is_array($input)) {
        foreach ($input as $key => $value) {
            if (!is_string($value))
                $input[$key] = replace_regx((array) $value, $otherRegx, $allowTags);
            else
                $input[$key] = preg_replace($regx, $replacement, $value);
        }
        return $input;
    }

    //Clean SELECT INSERT UPDATE DELETE UNION
    $input = preg_replace_callback(
        array('/\b(select)\b/i', '/\b(insert)\b/i', '/\b(update)\b/i', '/\b(delete)\b/i', '/\b(union)\b/i'),
        function ($matches) {

            $regxInd = array(
                'a' => '/a/',
                'c' => '/c/',
                'd' => '/d/',
                'e' => '/e/',
                'i' => '/i/',
                'l' => '/l/',
                'n' => '/n/',
                'o' => '/o/',
                'p' => '/p/',
                'r' => '/r/',
                's' => '/s/',
                't' => '/t/',
                'u' => '/u/',
                'A' => '/A/',
                'C' => '/C/',
                'D' => '/D/',
                'E' => '/E/',
                'I' => '/I/',
                'L' => '/L/',
                'N' => '/N/',
                'O' => '/O/',
                'P' => '/P/',
                'R' => '/R/',
                'S' => '/S/',
                'T' => '/T/',
                'U' => '/U/',
            );

            $replacementVal = array(
                'a' => '&#97;',
                'c' => '&#99;',
                'd' => '&#100;',
                'e' => '&#101;',
                'i' => '&#105;',
                'l' => '&#108;',
                'n' => '&#110;',
                'o' => '&#111;',
                'p' => '&#112;',
                'r' => '&#114;',
                's' => '&#115;',
                't' => '&#116;',
                'u' => '&#117;',
                'A' => '&#65;',
                'C' => '&#67;',
                'D' => '&#68;',
                'E' => '&#69;',
                'I' => '&#73;',
                'L' => '&#76;',
                'N' => '&#78;',
                'O' => '&#79;',
                'P' => '&#80;',
                'R' => '&#82;',
                'S' => '&#83;',
                'T' => '&#84;',
                'U' => '&#85;',
            );
            return preg_replace($regxInd, $replacementVal, $matches[0]);
        },
        $input
    );

    return preg_replace($regx, $replacement, $input);
}

function nl2brFilter($content)
{
    return nl2br(replace_regx($content));
}


/**
 *  Function:   num_to_word
 *
 *  Description:
 *  Converts a given number into
 *  alphabetical format ("one", "two", etc.)
 *
 *  @param $number
 *  @param $currency
 *  @param $currencyDec
 *  @param $decWord
 *  @return string
 *
 */
function num_to_word($number, $currency = ' Taka', $currencyDec = ' Poysa', $decWord = 'and')
{
    // ABS
    $number = abs($number);

    //Get the integer part
    $intpart = floor($number);

    //Get the fraction part
    $fraction = round($number - $intpart, 2);
    if ($fraction > 0)
        $fraction = substr($fraction, 2);
    //look([$intpart, $fraction]);
    $my_number = $intpart;
    if (($intpart < 0) || ($intpart > 999999999)) {
        throw new Exception("Number is out of range");
    }
    $Kt = floor($intpart / 10000000); /* Koti */
    $intpart -= $Kt * 10000000;
    $Gn = floor($intpart / 100000);  /* lakh  */
    $intpart -= $Gn * 100000;
    $kn = floor($intpart / 1000);     /* Thousands (kilo) */
    $intpart -= $kn * 1000;
    $Hn = floor($intpart / 100);      /* Hundreds (hecto) */
    $intpart -= $Hn * 100;
    $Dn = floor($intpart / 10);       /* Tens (deca) */
    $n = $intpart % 10;               /* Ones */
    $res = "";
    if ($Kt) {
        $res .= num_to_word($Kt, '') . " Koti ";
    }
    if ($Gn) {
        $res .= num_to_word($Gn, '') . " Lakh";
    }
    if ($kn) {
        $res .= (empty($res) ? "" : " ") .
            num_to_word($kn, '') . " Thousand";
    }
    if ($Hn) {
        $res .= (empty($res) ? "" : " ") .
            num_to_word($Hn, '') . " Hundred";
    }
    $ones = array(
        "",
        "One",
        "Two",
        "Three",
        "Four",
        "Five",
        "Six",
        "Seven",
        "Eight",
        "Nine",
        "Ten",
        "Eleven",
        "Twelve",
        "Thirteen",
        "Fourteen",
        "Fifteen",
        "Sixteen",
        "Seventeen",
        "Eighteen",
        "Nineteen"
    );
    $tens = array(
        "",
        "",
        "Twenty",
        "Thirty",
        "Forty",
        "Fifty",
        "Sixty",
        "Seventy",
        "Eighty",
        "Ninety"
    );
    if ($Dn || $n) {
        if (!empty($res)) {
            $res .= " and ";
        }
        if ($Dn < 2) {
            $res .= $ones[$Dn * 10 + $n];
        } else {
            $res .= $tens[$Dn];
            if ($n) {
                $res .= "-" . $ones[$n];
            }
        }
    }
    if (empty($res)) {
        $res = "zero";
    }

    if ((int)$fraction > 0) {
        $tmpDec = num_to_word($fraction, '');
        return $res . ' ' . $currency . ' ' . $decWord . ' ' . $tmpDec . $currencyDec;
    }
    return $res . $currency;
}
// file exist or not
if (!function_exists('isExist')) {
    function isExist($fileName, $folder = "")
    {
        if ($fileName != NULL && $fileName != '' && $fileName != '0' && file_exists(public_path() . DIRECTORY_SEPARATOR . $fileName) && !is_dir($fileName)) {
            return true;
        }
        return false;
    }
}
// delete file if exist
if (!function_exists('unlinkFile')) {
    function unlinkFile($fileName)
    {
        if (isExist($fileName)) {
            unlink(public_path() . DIRECTORY_SEPARATOR . $fileName);
        }
    }
}

// save all file and deleted if exist
if (!function_exists('filesSaveAndDelete')) {
    function filesSaveAndDelete(object $RequestObj, array $files, string $storePath, $oldModel = null)
    {
        $fileNames = [];
        foreach ($files as $file) {
            if ($RequestObj->hasFile($file)) {
                if ($oldModel != null) { // If model exist
                    if ($oldModel->$file != null) { //if model file exist
                        unlinkFile($oldModel->$file);
                    }
                }
                $fileName = imageName($RequestObj->$file->getClientOriginalName(), 1, $file);
                $fileNames[$file] = $RequestObj->$file->storeAs('uploads/' . $storePath, $fileName);
            }
        }
        return $fileNames;
    }
}

if (!function_exists('turncutText')) {
    function turncutText($text, $length = 0, $end = 1)
    {
        if ($end) {
            $name = substr($text, -$length);
        } else {
            $name = substr($text, 0, $length);
        }

        $name = (strlen($name) > 0) ? '...' . $name : '';
        return $name;
    }
}


if (!function_exists('destroyChangeLog')) {
    /**
     * Details: Remove the specified resource from storage.
     * @param ChangeLog $changeLog
     * @return RedirectResponse
     * @Author: Md. Abdullah <abdullah@systechdigital.com>
     * @Date: 20/09/2021
     * @Time: 12:57 PM
     */
    function destroyChangeLog(ChangeLog $changeLog)
    {
        try {
            if ($changeLog->modelable)
                $changeLog->modelable->forceDelete();
            $allLog = RecycleBin::where('model', $changeLog->model)->where('model_id', $changeLog->model_id)->get();
            foreach ($allLog as $key => $value) {
                $value->delete();
            }
            flash(__('Deleted successfully'))->success();
            return back();
        } catch (\Exception $e) {
            if (env('APP_DEBUG')) {
                dd($e);
            }
        }
        flash(__('Delete failed'))->error();
        return back();
    }
}


if (!function_exists('setChangeLogData')) {
    /**
     * add data to recycle bin.
     *
     * @param string $object
     * @param string $type
     * @param null $status
     * @return void
     */
    function setChangeLogData($object, $type, $status = NULL)
    {
        if ($status == NULL) {
            $status = 'approved';
        }

        $logData = [
            'data' => json_encode($object->toArray()),
            'type' => ChangeLog::TYPE[$type],
            'status' => ChangeLog::STATUS[$status],
            'created_by' => auth()->id(),
        ];

        $object->changes()->create($logData);
    }
}

if (!function_exists('get_static_content')) {
    /**
     * add data to recycle bin.
     *
     * @param string $object
     * @param string $type
     * @param null $status
     * @return void
     */
    function get_static_content($static_key)
    {
        $items = cache()->get('static_content')->where('key', $static_key)->first();
        return ($items) ? $items : '';
    }
}

if (!function_exists('get_parent_menu')) {
    /**
     * add data to recycle bin.
     *
     * @param string $object
     * @param string $type
     * @param null $status
     * @return void
     */
    function get_parent_menu()
    {
        if (get_current_menu() != null) {
            return get_public_menu()->flattenTree('child')->where('id', get_current_menu()->parent)->first();
        }
    }
}

if (!function_exists('get_current_menu')) {
    /**
     * add data to recycle bin.
     *
     * @param string $object
     * @param string $type
     * @param null $status
     * @return void
     */
    function get_current_menu()
    {
        $current_url = url()->current();
        return get_public_menu()->flattenTree('child')->where('link', $current_url)->first();
    }
}

if (!function_exists('get_public_menu')) {
    /**
     * add data to recycle bin.
     *
     * @param string $object
     * @param string $type
     * @param null $status
     * @return void
     */
    function get_public_menu()
    {
        cache()->forget('public_menu');
        $public_menu = cache()->remember('public_menu', Carbon::now()->addHours(2), function () {
            $public_menu = collect([]);
            $menu = Menus::where('id', 1)->with('items.child.child.child')->first();
            $public_menu = $menu->items;
            return $public_menu;
        });
        return $public_menu;
    }
}

/**
 * This function 
 *
 * @author      Md. Hossain Bhat <hossainbhat25@gmail.com>
 * @version     1.0
 * @see         
 * @since       12/04/2021
 * Time         15:19:36
 * @param       
 * @return      
 */

if (!function_exists('sendJson')) {
    function sendJson($statusCode = 200, $success = true, $code = 'A01', $payload = 'Successful!', $type = 'success', $fade = false)
    {
        return response()->json([
            'success' => $success,
            'code'    => $code,
            'payload' => $payload,
            'type'    => $type,
            'fade'    => $fade
        ], $statusCode);
    }
}
#end

/**
 * This function 
 *
 * @author      Md. Hossain Bhat <hossainbhat25@gmail.com>
 * @version     1.0
 * @see         
 * @since       03/21/2023
 * Time         12:23:18
 * @param       
 * @return      
 */
if (!function_exists('sendMessage')) {
    function sendMessage($message = "Success !!", $type = "success")
    {
        return sendJson(200, true, config('rest.response.success.code'), $message, $type);
    }
    #end
}


/**
 * This function return validation errors
 *
 * @author      Md. Hossain Bhat <hossainbhat25@gmail.com>
 * @version     1.0
 * @see
 * @since       01/28/2021
 * Time         10:34:28
 * @param       $errors
 * @return      validation errors
 */
function sendValidationError($errors, $message = 'The given data was invalid.')
{
    return sendJson(422, false, config('rest.response.validation_error.code'), $errors, 'error', $message);
}
#end

/**
 * This function works on Login Required json
 *
 * @author      Md. Hossain Bhat <hossainbhat25@gmail.com>
 * @version     1.0
 * @see
 * @since       01/28/2021
 * Time         10:34:28
 * @param       $errors
 * @return      Login Required json
 */
function sendUnauthorizedError($errors, $message = 'Unauthorized Access.')
{
    $errors = [
        'message' => [$message]
    ];
    return sendJson(401, false, config('rest.response.unauthorized.code'), $errors, 'error', $message);
}
#end

/**
 * This function works on Temporary Redirect
 *
 * @author      Md. Hossain Bhat <hossainbhat25@gmail.com>
 * @version     1.0
 * @see
 * @since       01/28/2021
 * Time         10:34:28
 * @param       $errors
 * @return      Temporary Redirect
 */
function sendEmailVarifiedError($message = 'Please Verify your email address.')
{
    $errors = [
        'message' => [$message]
    ];
    return sendJson(307, false, config('rest.response.login.verify_email.code'), $errors, 'error', $message);
}
#end

/**
 * This function 
 *
 * @author      Md. Hossain Bhat <hossainbhat25@gmail.com>
 * @version     1.0
 * @see         
 * @since       04/23/2022
 * Time         11:47:34
 * @param       
 * @return      
 */
function sendAddSuccess($data, $message = "Successfully Added")
{
    # code...   
    $payload = [
        'data' => $data
    ];
    return sendJson(200, true, config('rest.response.success.code'), $payload, 'success', $message);
}
#end

/**
 * This function 
 *
 * @author      Md. Hossain Bhat <hossainbhat25@gmail.com>
 * @version     1.0
 * @see         
 * @since       04/23/2022
 * Time         11:47:34
 * @param       
 * @return      
 */
function sendUpdateSuccess($data, $message = "Successfully Updated")
{
    # code...   
    $payload = [
        'data' => $data
    ];
    return sendJson(200, true, config('rest.response.success.code'), $payload, 'success', $message);
}
#end

/**
 * This function 
 *
 * @author      Md. Hossain Bhat <hossainbhat25@gmail.com>
 * @version     1.0
 * @see         
 * @since       04/23/2022
 * Time         11:47:34
 * @param       
 * @return      
 */
function sendDeleteSuccess($data = [])
{
    $payload = [
        'item' => $data
    ];
    $message = "Successfully deleted";
    return sendJson(200, true, config('rest.response.success.code'), $payload, 'success', $message);
}
#end

/**
 * This function returns success message
 *
 * @author      Md. Hossain Bhat <hossainbhat25@gmail.com>
 * @version     1.0
 * @see
 * @since       01/28/2021
 * Time         10:45:54
 * @param       $message
 * @return      Success message
 */
function sendSuccess($payload, $message = "Success")
{
    return sendJson(200, true, config('rest.response.success.code'), $payload, 'success', $message);
}
#end

/**
 * This function return invalid message
 *
 * @author      Md. Hossain Bhat <hossainbhat25@gmail.com>
 * @version     1.0
 * @see
 * @since       01/28/2021
 * Time         11:09:36
 * @param       $message
 * @return      invalid message
 */
function sendInvalid($message = "Wrong email or password!")
{
    $payload = [
        'message' => [$message]
    ];
    return sendJson(403, false, config('rest.response.login.invalid.code'), $payload, 'error', $message);
}
#end

/**
 * This function  return error
 *
 * @author      Md. Hossain Bhat <hossainbhat25@gmail.com>
 * @version     1.0
 * @see
 * @since       01/28/2021
 * Time         11:23:39
 * @param
 * @return
 */
function sendNotFound($message = "Not found. Try another one")
{
    $payload = [
        'message' => $message
    ];
    return sendJson(404, false, config('rest.response.error.code'), $payload, 'error', $message);
}
#end

/**
 * This function  return error
 *
 * @author      Md. Hossain Bhat <hossainbhat25@gmail.com>
 * @version     1.0
 * @see
 * @since       01/28/2021
 * Time         11:23:39
 * @param
 * @return
 */
function sendError($payload, $message = "Error")
{
    return sendJson(400, false, config('rest.response.error.code'), $payload, 'error', $message);
}

#end

/**
 * This function 
 *
 * @author      Md. Hossain Bhat <hossainbhat25@gmail.com>
 * @version     1.0
 * @see         
 * @since       04/23/2022
 * Time         12:13:25
 * @param       
 * @return      
 */
function sendPermissionError($message = 'Permission denied. Please contact your admin.')
{
    $payload = [
        'message' => $message
    ];
    return sendJson(403, false, config('rest.response.error.code'), $payload, 'error', $message);
}
#end



/**
 * This function return number with two decimal
 *
 * @author      Md. Hossain Bhat <hossainbhat25@gmail.com>
 * @version     1.0
 * @see         
 * @since       12/04/2021
 * Time         15:19:36
 * @param       $digit = 0, $pad = 2
 * @return      2.00
 */

if (!function_exists('number_digit_pad')) {

    function number_digit_pad($digit = 0, $pad = 2)
    {
        return number_format((float)$digit, $pad, '.', '');
    }
}
#end

/**
 * This function return all Days (Mar 01 Mar 02)
 *
 * @author      Md. Hossain Bhat <hossainbhat25@gmail.com>
 * @version     1.0
 * @see         
 * @since       12/04/2021
 * Time         15:19:36
 * @param       $month = 2, $year = 2022
 * @return      2.00
 */

if (!function_exists('allDaysInMonthByMonthYear')) {

    function allDaysInMonthByMonthYear($month = 2, $year = 2022)
    {
        $data = [];
        for ($i = 1; $i <= cal_days_in_month(CAL_GREGORIAN, $month, $year); $i++) {
            $stringMonth = date('M', mktime(0, 0, 0, $month, 10));
            $item = "$stringMonth $i";
            array_push($data, $item);
        }
        return $data;
    }
}
#end

/**
 * This function return true and false if match
 *
 * @author      Md. Hossain Bhat <hossainbhat25@gmail.com>
 * @version     1.0
 * @see         
 * @since       02/24/2022
 * Time         16:31:29
 * @param       $dataArray, $search_value, $key_to_search
 * @return      true false
 */
if (!function_exists('searchTwoDArrayItemSearch')) {
    function searchTwoDArrayItemSearch($dataArray, $search_value, $key_to_search)
    {
        foreach ($dataArray as $key => $cur_value) {
            if ($cur_value[$key_to_search] == $search_value) {
                return $dataArray[$key];
            }
        }
        return false;
    }
}
#end

/**
 * This function return true and false if match
 *
 * @author      Md. Hossain Bhat <hossainbhat25@gmail.com>
 * @version     1.0
 * @see         
 * @since       02/24/2022
 * Time         16:31:29
 * @param       $dataArray, $search_value, $key_to_search
 * @return      true false
 */
if (!function_exists('employeeIdFormat')) {
    function  employeeIdFormat($number)
    {
        $settings = Utility::settings();
        return $settings["employee_prefix"] . sprintf("%05d", $number);
    }
}
#end

/**
 * This function return user date formate
 *
 * @author      Md. Hossain Bhat <hossainbhat25@gmail.com>
 * @version     1.0
 * @see         
 * @since       02/22/2023
 * Time         15:32:07
 * @param       
 * @return      
 */
if (!function_exists('dateFormat')) {
    function dateFormat($date)
    {
        $settings = Utility::settings();
        return date($settings['site_date_format'], strtotime($date));
    }
    #end
}

/**
 * This function return employee id format
 *
 * @author      Md. Hossain Bhat <hossainbhat25@gmail.com>
 * @version     1.0
 * @see         
 * @since       02/22/2023
 * Time         16:18:56
 * @param       
 * @return      
 */
if (!function_exists('employeeIdFormat')) {
    function employeeIdFormat($number)
    {
        $settings = Utility::settings();
        return $settings["employee_prefix"] . sprintf("%05d", $number);
    }
}
#end


/**
 * This function return tk format
 *
 * @author      Md. Hossain Bhat <hossainbhat25@gmail.com>
 * @version     1.0
 * @see         
 * @since       04/27/2023
 * Time         11:53:19
 * @param       
 * @return      
 */
if (!function_exists('tk_format')) {
    function tk_format($amount)
    {
        $amount = preg_replace("/(\d+?)(?=(\d\d)+(\d)(?!\d))(\.\d+)?/i", "$1,", $amount);
        return $amount;
    }
}
#end

/**
 * This function 
 *
 * @author      Md. Hossain Bhat <hossainbhat25@gmail.com>
 * @version     1.0
 * @see         
 * @since       04/27/2023
 * Time         15:10:01
 * @param       
 * @return      
 */
if (!function_exists('num_to_word')) {

    function num_to_word($number, $currency = ' Taka', $currencyDec = ' Poysa', $decWord = 'and')
    {
        // ABS
        $number = abs($number);

        //Get the integer part
        $intpart = floor($number);

        //Get the fraction part
        $fraction = round($number - $intpart, 2);
        if ($fraction > 0)
            $fraction = substr($fraction, 2);
        //look([$intpart, $fraction]);
        $my_number = $intpart;
        if (($intpart < 0) || ($intpart > 999999999)) {
            throw new Exception("Number is out of range");
        }
        $Kt = floor($intpart / 10000000); /* Koti */
        $intpart -= $Kt * 10000000;
        $Gn = floor($intpart / 100000);  /* lakh  */
        $intpart -= $Gn * 100000;
        $kn = floor($intpart / 1000);     /* Thousands (kilo) */
        $intpart -= $kn * 1000;
        $Hn = floor($intpart / 100);      /* Hundreds (hecto) */
        $intpart -= $Hn * 100;
        $Dn = floor($intpart / 10);       /* Tens (deca) */
        $n = $intpart % 10;               /* Ones */
        $res = "";
        if ($Kt) {
            $res .= num_to_word($Kt, '') . " Koti ";
        }
        if ($Gn) {
            $res .= num_to_word($Gn, '') . " Lakh";
        }
        if ($kn) {
            $res .= (empty($res) ? "" : " ") .
                num_to_word($kn, '') . " Thousand";
        }
        if ($Hn) {
            $res .= (empty($res) ? "" : " ") .
                num_to_word($Hn, '') . " Hundred";
        }
        $ones = array(
            "",
            "One",
            "Two",
            "Three",
            "Four",
            "Five",
            "Six",
            "Seven",
            "Eight",
            "Nine",
            "Ten",
            "Eleven",
            "Twelve",
            "Thirteen",
            "Fourteen",
            "Fifteen",
            "Sixteen",
            "Seventeen",
            "Eighteen",
            "Nineteen"
        );
        $tens = array(
            "",
            "",
            "Twenty",
            "Thirty",
            "Forty",
            "Fifty",
            "Sixty",
            "Seventy",
            "Eighty",
            "Ninety"
        );
        if ($Dn || $n) {
            if (!empty($res)) {
                $res .= " and ";
            }
            if ($Dn < 2) {
                $res .= $ones[$Dn * 10 + $n];
            } else {
                $res .= $tens[$Dn];
                if ($n) {
                    $res .= "-" . $ones[$n];
                }
            }
        }
        if (empty($res)) {
            $res = "zero";
        }

        if ((int)$fraction > 0) {
            $tmpDec = num_to_word($fraction, '');
            return $res . ' ' . $currency . ' ' . $decWord . ' ' . $tmpDec . $currencyDec;
        }
        return $res . $currency;
    }
}
#end


/**
 * This function return time format from second
 *
 * @author      Md. Hossain Bhat <hossainbhat25@gmail.com>
 * @version     1.0
 * @see         
 * @since       05/28/2023
 * Time         16:37:45
 * @param       
 * @return      
 */

if (!function_exists('second_to_time')) {
    function second_to_time($time)
    {
        $hours            = floor($time / 3600);
        $mins             = floor($time / 60 % 60);
        $secs             = floor($time % 60);
        return sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
    }
}
#end



/**
 * This function generate calendar year
 *
 * @author      Md. Hossain Bhat <hossainbhat25@gmail.com>
 * @version     1.0
 * @see         
 * @since       03/14/2023
 * Time         21:16:08
 * @param       
 * @return      
 */
if (!function_exists('get_calendar_years')) {
    function get_calendar_years($before_current_year = 5, $after_current_year = 10)
    {
        $current_year = date('Y');
        $starting_year = $current_year - $before_current_year;
        $ending_year = $current_year + $after_current_year;
        $calendar_years = [];
        for ($starting_year; $starting_year <= $ending_year; $starting_year++) {
            array_push($calendar_years, $starting_year);
        }
        return $calendar_years;
    }
}
#end

/**
 * This function 
 *
 * @author      Md. Hossain Bhat <hossainbhat25@gmail.com>
 * @version     1.0
 * @see         
 * @since       03/24/2023
 * Time         14:21:54
 * @param       
 * @return      
 */
if (!function_exists('get_days_name')) {
    function get_days_name()
    {
        $days = [
            "Sunday",
            "Monday",
            "Tuesday",
            "Wednesday",
            "Thursday",
            "Friday",
            "Saturday"
        ];
        return $days;
    }
    #end
}

/**
 * This function 
 *
 * @author      Md. Hossain Bhat <hossainbhat25@gmail.com>
 * @version     1.0
 * @see         
 * @since       05/31/2023
 * Time         10:30:45
 * @param       
 * @return      
 */
if (!function_exists('get_holiday_by_month')) {
    function get_holiday_by_month($month, $year)
    {
        // calculate days in month
        $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        // prepare start of the month
        $year_month_start = "$year-$month-01";
        // prepare end of the month
        $year_month_end = "$year-$month-$days_in_month";
        // get holiday base on month and year on behalf of start date of db
        $holidays = Holiday::whereBetween('start_date', [$year_month_start, $year_month_end])->get();
        // convert last day of month year into time
        $year_month_end_time = strtotime($year_month_end);
        $total_days = 0;
        foreach ($holidays as $key => $holiday) {
            $start_date = $holiday->start_date;
            $end_date = $holiday->end_date;
            // convert start date into time
            $start_date_time = strtotime($start_date);
            // convert end date into time
            $end_date_time = strtotime($end_date);
            // check db end date bigger thant current month date. if bigger update end date 
            if ($year_month_end_time < $end_date_time) {
                $end_date_time = $year_month_end_time;
            }
            if ($end_date_time == $start_date_time) {
                $total_days += 1;
            } else {
                // 1 day=86400 second
                $total_days += ceil(abs($end_date_time - $start_date_time) / 86400);
            }
        }
        return $total_days;
    }
}
#end


/**
 * This function return all days between two dates
 *
 * @author      Md. Hossain Bhat <hossainbhat25@gmail.com>
 * @version     1.0
 * @see         
 * @since       05/31/2023
 * Time         11:43:56
 * @param       
 * @return      
 */
if (!function_exists('get_date_list_between_two_dates')) {
    function get_date_list_between_two_dates($start_date, $end_date)
    {
        $days = [];
        $start_date_time = strtotime($start_date);
        $end_date_time = strtotime($end_date);
        $days[] = $start_date;
        // if date different
        while ($start_date_time < $end_date_time) {
            // next one day= 86400 second
            $start_date_time += 86400;
            $days[] = date('Y-m-d', $start_date_time);
        }
        return $days;
    }
}
#end


/**
 * This function return time from hh:mm:ss
 *
 * @author      Md. Hossain Bhat <hossainbhat25@gmail.com>
 * @version     1.0
 * @see         
 * @since       06/05/2023
 * Time         17:02:35
 * @param       
 * @return      
 */
if (!function_exists('hour_minute_second_to_second')) {
    function hour_minute_second_to_second($time)
    {
        list($hr, $min, $sec) = explode(':', $time);
        return (((int)$hr) * 60 * 60) + (((int)$min) * 60) + ((int)$sec);
    }
    #end
}

/**
 * This function return hour from second
 *
 * @author      Md. Hossain Bhat <hossainbhat25@gmail.com>
 * @version     1.0
 * @see         
 * @since       06/05/2023
 * Time         17:15:08
 * @param       
 * @return      
 */
if (!function_exists('second_to_hour')) {
    function second_to_hour($second)
    {
        return floor($second / 3600);
    }
}
#end


