<?php

namespace PicupTechnologies\PicupPHPApi\Collections;

use JsonSerializable;

final class ThirdPartyCollection implements JsonSerializable
{
    private $thirdParties = [];

    /**
     * @return mixed
     */
    public function getThirdParties()
    {
        return $this->thirdParties;
    }

    /**
     * @param mixed $thirdParties
     */
    public function setThirdParties($thirdParties): void
    {
        $this->thirdParties = $thirdParties;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->thirdParties;
    }
}
