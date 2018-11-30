<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 12/10/2018
 * Time: 23:40
 */

namespace ZXC\Interfaces\Modules\Auth;


interface OAuth2
{
    public function buildURI(array $data);

    public function getToken(array $data);

    public function refreshToken(array $data);
}