<?php


namespace System\Libs;


class Password
{

    /**
     * generate password hash
     * @param string $password
     * @return bool|string
     */
    public static function password_hash(string $password) {
        if (!function_exists('crypt')) {
            die("Crypt must be loaded for password_hash to function");
        }

        $resultLength = 0;

        $cost = 10;//default cost

        $raw_salt_len = 16;
        $required_salt_len = 22;
        $hash_format = sprintf("$2y$%02d$", $cost);
        $resultLength = 60;

        $salt_req_encoding = false;

        $buffer = '';
        $buffer_valid = false;

        if (function_exists('mcrypt_create_iv')) {
            $buffer = mcrypt_create_iv($raw_salt_len, MCRYPT_DEV_URANDOM);
            if ($buffer) {
                $buffer_valid = true;
            }
        }

        if (!$buffer_valid && function_exists('openssl_random_pseudo_bytes')) {
            $strong = false;
            $buffer = openssl_random_pseudo_bytes($raw_salt_len, $strong);
            if ($buffer && $strong) {
                $buffer_valid = true;
            }
        }

        if (!$buffer_valid && @is_readable('/dev/urandom')) {
            $file = fopen('/dev/urandom', 'r');
            $read = 0;
            $local_buffer = '';
            while ($read < $raw_salt_len) {
                $local_buffer .= fread($file, $raw_salt_len - $read);
                $read = Validation::strlen_8bit($local_buffer);
            }
            fclose($file);
            if ($read >= $raw_salt_len) {
                $buffer_valid = true;
            }
            $buffer = str_pad($buffer, $raw_salt_len, "\0") ^ str_pad($local_buffer, $raw_salt_len, "\0");
        }

        if (!$buffer_valid || Validation::strlen_8bit($buffer) < $raw_salt_len) {
            $buffer_length = Validation::strlen_8bit($buffer);
            for ($i = 0; $i < $raw_salt_len; $i++) {
                if ($i < $buffer_length) {
                    $buffer[$i] = $buffer[$i] ^ chr(mt_rand(0, 255));
                } else {
                    $buffer .= chr(mt_rand(0, 255));
                }
            }
        }

        $salt = $buffer;
        $salt_req_encoding = true;


        if ($salt_req_encoding) {
            $base64_digits = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
            $bcrypt64_digits = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            $base64_string = base64_encode($salt);
            $salt = strtr(rtrim($base64_string, '='), $base64_digits, $bcrypt64_digits);
        }

        $salt = Validation::substr_8bit($salt, 0, $required_salt_len);
        $hash = $hash_format . $salt;
        $ret = crypt($password, $hash);

        if (!is_string($ret) || Validation::strlen_8bit($ret) != $resultLength) {
            return false;
        }
        return $ret;
    }

    /**
     * @param $hash
     * @return array
     */
    public static function password_get_info(string $hash) :array
    {
        $return = array(
            'algo' => 0,
            'algoName' => 'unknown',
            'options' => array(),
        );

        if (Validation::substr_8bit($hash, 0, 4) == '$2y$' && Validation::strlen_8bit($hash) == 60) {
            $return['algo'] = PASSWORD_BCRYPT;
            $return['algoName'] = 'bcrypt';
            list($cost) = sscanf($hash, "$2y$%d$");
            $return['options']['cost'] = $cost;
        }

        return $return;
    }

    /**
     * @param string $hash
     * @return bool
     */
    public static function password_needs_rehash(string $hash) :bool
    {
        $info = self::password_get_info($hash);

//        if ($info['algo'] !== (int) $algo) {
//            return true;
//        }

        $cost = PASSWORD_BCRYPT_DEFAULT_COST;
        if ($cost !== $info['options']['cost']) {
            return true;
        }

        return false;

    }
}