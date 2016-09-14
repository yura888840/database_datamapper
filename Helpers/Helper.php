<?php

namespace Migration\Helpers;

class Helper
{
    private $updateDb;

    private $configData;

    public function __construct($configData, $updateDb)
    {
        $this->configData   = $configData;
        $this->updateDb     = $updateDb;
    }

    public function encryptEmailHelper($email)
    {
        $encryptedEmail = $email;

        //@todo email encyption

        return ' EMAIL: '. $encryptedEmail;
    }

    public function encryptEmailHasherHelper($email)
    {
        $encryptedEmailHash = $email;

        //@todo email hash encyption

        return 'HASHED ' . $encryptedEmailHash;
    }

    public function statusTransformer($status)
    {
        //'enabled','disabled','banned','waiting_confirmation'
        $statusMapper = [
            'active'                => 'enabled',
            'deactivated'           => 'disabled',
            'awaiting_confirmation' => 'waiting_confirmation',
        ];

        if(array_key_exists($status, $statusMapper)) {
            $statusTransformed = $statusMapper[$status];
        } else {
            $statusTransformed = 'default';
        }

        return $statusTransformed;
    }

    public function newsletterTransform($newsletter)
    {
        $newsletterMapper = [
            'true'  => 1,
            'false' => 0
        ];

        if(array_key_exists($newsletter, $newsletterMapper)) {
            $transformedNewsletter = $newsletterMapper[$newsletter];
        } else {
            $transformedNewsletter = 0;
        }

        return $transformedNewsletter;
    }

    public function countryMapperHelper($countryAbbrev)
    {
        $countryAbbrev = strtoupper($countryAbbrev);

        $sql =<<<SQL
SELECT id_country FROM country WHERE country_code = "$countryAbbrev"
SQL;
        $stmt = $this->updateDb->prepare($sql);

        try {
            $result = $stmt->execute();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }

        if ($result) {
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $result['id_country'];
        }

        return NULL;
    }
}
