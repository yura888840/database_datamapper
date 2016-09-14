<?php

namespace Migration;

class InputUserDataAdapter
{
    private $data = [];

    public function mapPartOfData($data)
    {
        foreach ($data as $k => $v) {
            switch($k) {
                case 'users' :
                    $this->processUsers($v);
                    break;
                case 'user_contracts':
                    $this->processUserContracts($v);
                    break;
                case 'api_user_contracts':
                    $this->processApiUserContracts($v);
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * Processing data from Users SELECT
     *
     * @param $data array
     */
    private function processUsers($data)
    {
        foreach ($data as $row) {
            $this->data[$row['id_user']] = array_merge(
                $row,
                [
                    'user_contracts' => [],
                    'api_user_contracts' => [],
                ]
            );
        }
    }

    private function processUserContracts($data)
    {
        foreach ($data as $row) {
            $idUser = $row['id_user'];

            $idContract = $row['id_contract'];

            if(empty($idContract)) {
                continue;
            }

            if (!array_key_exists($idUser, $this->data)) {
                $this->data[$idUser] =
                    [
                        'user_contracts' => [],
                        'api_user_contracts' => [],
                    ];
            }


            if (!array_key_exists($idContract, $this->data[$idUser]['user_contracts'])) {
                $this->data[$idUser]['user_contracts'][$idContract] = [
                    'id_contract'   => $idContract,
                    'id_user'       => $row['id_user'],
                    'date_start'    => $row['date_start'],
                    'date_end'      => $row['date_end'],
                    'type'          => $row['type'],
                    'txn_id'        => $row['status_contract'],

                    'time_spent'    => []
                ];
            }

            $idTimeSpent = $row['id_time_spent'];

            $timeSpentUnit = [
                'id_time_spent' => $row['id_time_spent'],
                'id_contract'   => $row['id_contract'],
                'date'          => $row['date'],
                'job_info'      => $row['job_info'],
            ];

            if(!empty($idTimeSpent)) {
                $this->data[$idUser]['user_contracts'][$idContract]['time_spent'][$idTimeSpent] = $timeSpentUnit;
            }
        }
    }

    private function processApiUserContracts($data)
    {
        foreach ($data as $row) {
            $idUser = $row['id_user'];

            if(empty($idUser)) {
                continue;
            }

            $idContract = $row['id_contract'];

            if (!array_key_exists($idUser, $this->data)) {
                $this->data[$idUser] =
                    [
                        'user_contracts' => [],
                        'api_user_contracts' => [],
                    ];
            }

            if (!array_key_exists($idContract, $this->data[$idUser]['api_user_contracts'])) {
                $this->data[$idUser]['api_user_contracts'][$idContract] = [
                    'id_contract'               => $idContract,
                    'id_user'                   => $row['id_user'],
                    'id_payment_information'    => $row['id_payment_information'],
                    'price'                     => $row['price'],
                    'minutes_bought'            => $row['minutes_bought'],
                    'auto_renew'                => $row['auto_renew'],
                    'type'                      => $row['type'],
                    'status'                    => $row['status'],
                    'date_created'              => $row['date_created'],

                    'payment_information'       => [],
                    'time_spent'                => []
                ];
            }

            $idTimeSpent = $row['id_time_spent'];

            $timeSpentUnit = [
                'id_time_spent' => $row['id_time_spent'],
                'id_contract'   => $row['id_contract'],
                'date'          => $row['date'],
                'job_info'      => $row['job_info'],
            ];

            if(!empty($idTimeSpent)) {
                $this->data[$idUser]['api_user_contracts'][$idContract]['time_spent'][$idTimeSpent] = $timeSpentUnit;
            }

            $idPaymentInformation = $row['id_payment_information'];

            if(!empty($idPaymentInformation)) {
                $paymentUnit = [
                    'id_payment_information'    => $row['id_payment_information'],
                    'id_user'                   => $row['id_user'],
                    'token'                     => $row['token'],
                    'payment_id'                => $row['payment_id'],
                    'raw_data'                  => $row['raw_data'],
                    'payment_parameters'        => $row['payment_parameters'],
                    'used'                      => $row['used'],
                    'date_created'              => $row['date_created'],
                ];

                $this->data
                [$idUser]
                ['api_user_contracts']
                [$idContract]
                ['payment_information']
                [$idPaymentInformation] = $paymentUnit;
            }
        }
    }

    public function getData()
    {
        return $this->data;
    }
}
