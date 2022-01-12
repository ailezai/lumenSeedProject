<?php
namespace AiLeZai\Common\Lib\Jwt\Api;


class UserAuthData
{
    /** @var int ax-user系统的统一用户id */
    private static $axUid = null;

    /** @var string 记加班6码 */
    private static $jjbUid = null;

    /** @var int 打工圈用户id (已注册用户才有) */
    private static $dgqUid = null;

    /** @var int 打工贷用户id (已注册用户才有) */
    private static $dgdUid = null;

    /** @var int token生成时间戳; 不考虑刷新的话, 近似等于最后登录时间 */
    private static $iat = null;

    /**
     * @param $payloadArr array jwt的payload解析出的数组, dgqUid,dgdUid可能不存在
     */
    public static function initByJwtPayload($payloadArr)
    {
        self::$axUid = $payloadArr['axUid'] ?? null;
        self::$jjbUid = $payloadArr['jjbUid'] ?? null;
        self::$dgqUid = $payloadArr['dgqUid'] ?? null;
        self::$dgdUid = $payloadArr['dgdUid'] ?? null;
        self::$iat = $payloadArr['iat'] ?? null;
    }

    /**
     * @param $uid int 记加班的用户id (注意: 不是记加班6码, 而且无法反向解析出记加班6码)
     * @param $loginTime int 在记加班的最后登录时间戳
     */
    public static function initByJjbUserinfo($uid, $loginTime)
    {
        self::$axUid = $uid;
        self::$jjbUid = null; // 记加班用户id 和 记加班6码 不完全是一一对应的. 这里无法直接解析
        self::$dgqUid = null;
        self::$dgdUid = null;
        self::$iat = $loginTime;
    }

    /**
     * @param $uid int 打工圈的用户id
     * @param $loginTime int 在打工圈的最后登录时间戳
     */
    public static function initByDgqUserinfo($uid, $loginTime)
    {
        self::$axUid = null;
        self::$jjbUid = null;
        self::$dgqUid = $uid;
        self::$dgdUid = null;
        self::$iat = $loginTime;
    }

    /**
     * @param $uid int 打工贷的用户id
     * @param $loginTime int 在打工贷的最后登录时间戳
     */
    public static function initByDgdUserinfo($uid, $loginTime)
    {
        self::$axUid = null;
        self::$jjbUid = null;
        self::$dgqUid = null;
        self::$dgdUid = $uid;
        self::$iat = $loginTime;
    }


    /**
     * @return int
     */
    public static function getAxUid()
    {
        return intval(self::$axUid);
    }

    /**
     * @return string
     */
    public static function getJjbUid()
    {
        return strval(self::$jjbUid);
    }

    /**
     * @return int
     */
    public static function getDgqUid()
    {
        return intval(self::$dgqUid);
    }

    /**
     * @return int
     */
    public static function getDgdUid()
    {
        return intval(self::$dgdUid);
    }

    /**
     * @return int
     */
    public static function getIssuedAt()
    {
        return intval(self::$iat);
    }

}
