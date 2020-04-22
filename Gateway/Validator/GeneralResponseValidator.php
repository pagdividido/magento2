<?php
/**
 * Copyright © Fluxx. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Fluxx\Magento2\Gateway\Validator;

use Fluxx\Magento2\Gateway\SubjectReader;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

/**
 * Class GeneralResponseValidator
 */
class GeneralResponseValidator extends AbstractValidator
{
    /**
     * The result code
     */
    private const RESULT_CODE_SUCCESS = '1';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var ResultInterfaceFactory
     */
    private $resultFactory;

    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        SubjectReader $subjectReader
    ) {
        parent::__construct($resultFactory);
        $this->resultFactory = $resultFactory;
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function validate(array $validationSubject): ResultInterface
    {
        $response = $this->subjectReader->readResponse($validationSubject);
        $isValid = $response['RESULT_CODE'];
        $errorCodes = [];
        $errorMessages = [];

        if (!$isValid) {
            if (isset($response['message']['code'])) {
                $errorCodes[] = $response['message']['code'];
                $errorMessages[] = $response['message']['text'];
            } elseif (isset($response['error'])) {
                foreach ($response['error'] as $typeEnty => $typeEntyValue) {
                    foreach ($typeEntyValue as $typeEntyCode => $typeEntyCodeValue) {
                        $errorCodes[] = $typeEntyCode;
                        $errorMessages[] = $typeEntyCodeValue[0];
                    }
                }
            }
        }

        return $this->createResult($isValid, $errorMessages, $errorCodes);
    }
}