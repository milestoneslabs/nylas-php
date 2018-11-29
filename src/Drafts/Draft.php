<?php namespace Nylas\Drafts;

use Nylas\Utilities\API;
use Nylas\Utilities\Options;
use Nylas\Utilities\Validate as V;

/**
 * ----------------------------------------------------------------------------------
 * Nylas Drafts
 * ----------------------------------------------------------------------------------
 *
 * @author lanlin
 * @change 2018/11/23
 */
class Draft
{

    // ------------------------------------------------------------------------------

    /**
     * @var \Nylas\Utilities\Options
     */
    private $options;

    // ------------------------------------------------------------------------------

    /**
     * Draft constructor.
     *
     * @param \Nylas\Utilities\Options $options
     */
    public function __construct(Options $options)
    {
        $this->options = $options;
    }

    // ------------------------------------------------------------------------------

    /**
     * get drafts list
     *
     * @param string $anyEmail
     * @param string $accessToken
     * @return array
     */
    public function getDraftsList(string $anyEmail = null, string $accessToken = null)
    {
        $params = ['access_token' => $accessToken ?? $this->options->getAccessToken()];

        !empty($anyEmail) AND $params['any_email'] = $anyEmail;

        $rule = V::keySet(
            V::key('access_token', V::stringType()->notEmpty()),
            V::keyOptional('any_email', V::arrayVal()->each(V::email(), V::intType()))
        );

        V::doValidate($rule, $params);

        $emails = implode(',', $params['any_email'] ?? []);
        $header = ['Authorization' => $params['access_token']];
        $query  = empty($emails) ? [] : ['any_email' => $emails];

        return $this->options
        ->getRequest()
        ->setQuery($query)
        ->setHeaderParams($header)
        ->get(API::LIST['drafts']);
    }

    // ------------------------------------------------------------------------------

    /**
     * get draft
     *
     * @param string $draftId
     * @param string $accessToken
     * @return array
     */
    public function getDraft(string $draftId, string $accessToken = null)
    {
        $params =
        [
            'id'           => $draftId,
            'access_token' => $accessToken ?? $this->options->getAccessToken()
        ];

        $rule = V::keySet(
            V::key('id', V::stringType()->notEmpty()),
            V::key('access_token', V::stringType()->notEmpty())
        );

        V::doValidate($rule, $params);

        $header = ['Authorization' => $params['access_token']];

        return $this->options
        ->getRequest()
        ->setPath($params['id'])
        ->setHeaderParams($header)
        ->get(API::LIST['oneDraft']);
    }

    // ------------------------------------------------------------------------------

    /**
     * add draft
     *
     * @param array $params
     * @return array
     */
    public function addDraft(array $params)
    {
        $rules = $this->getBaseRules();

        $params['access_token'] =
        $params['access_token'] ?? $this->options->getAccessToken();

        V::doValidate(V::keySet(...$rules), $params);

        $header = ['Authorization' => $params['access_token']];

        unset($params['access_token']);

        return $this->options
        ->getRequest()
        ->setFormParams($params)
        ->setHeaderParams($header)
        ->post(API::LIST['drafts']);
    }

    // ------------------------------------------------------------------------------

    /**
     * update draft
     *
     * @param array $params
     * @return array
     */
    public function updateDraft(array $params)
    {
        $rules = $this->getUpdateRules();

        $params['access_token'] =
        $params['access_token'] ?? $this->options->getAccessToken();

        V::doValidate(V::keySet(...$rules), $params);

        $path   = $params['id'];
        $header = ['Authorization' => $params['access_token']];

        unset($params['id'], $params['access_token']);

        return $this->options
        ->getRequest()
        ->setPath($path)
        ->setFormParams($params)
        ->setHeaderParams($header)
        ->put(API::LIST['oneDraft']);
    }

    // ------------------------------------------------------------------------------

    /**
     * delete draft
     *
     * @param array $params
     * @return mixed
     */
    public function deleteDraft(array $params)
    {
        $params['access_token'] = $params['access_token'] ?? $this->options->getAccessToken();

        $rule = V::keySet(
            V::key('id', V::stringType()->notEmpty()),
            V::key('version', V::stringType()->notEmpty()),
            V::key('access_token', V::stringType()->notEmpty())
        );

        V::doValidate($rule, $params);

        $path   = $params['id'];
        $header = ['Authorization' => $params['access_token']];

        unset($params['id'], $params['access_token']);

        return $this->options
        ->getRequest()
        ->setPath($path)
        ->setFormParams($params)
        ->setHeaderParams($header)
        ->delete(API::LIST['oneDraft']);
    }

    // ------------------------------------------------------------------------------

    /**
     * array of string
     *
     * @return \Respect\Validation\Validator
     */
    private function arrayOfString()
    {
        return V::arrayVal()->each(
            V::stringType()->notEmpty(),
            V::intType()
        );
    }

    // ------------------------------------------------------------------------------

    /**
     * array of object
     *
     * @return \Respect\Validation\Validator
     */
    private function arrayOfObject()
    {
        return V::arrayType()->each(
            V::keySet(
                V::key('name', V::stringType(), false),
                V::key('email', V::email())
            ),
            V::intType()
        );
    }

    // ------------------------------------------------------------------------------

    /**
     * rules for update
     *
     * @return array
     */
    private function getUpdateRules()
    {
        $rules = $this->getBaseRules();

        $update =
        [
            V::key('id', V::stringType()->notEmpty()),
            V::key('version', V::stringType()->length(1, null))
        ];

        return array_merge($rules, $update);
    }

    // ------------------------------------------------------------------------------

    /**
     * draft base validate rules
     *
     * @return array
     */
    private function getBaseRules()
    {
        return
        [
            V::keyOptional('to', $this->arrayOfObject()),
            V::keyOptional('cc', $this->arrayOfObject()),
            V::keyOptional('bcc', $this->arrayOfObject()),
            V::keyOptional('from', $this->arrayOfObject()),
            V::keyOptional('reply_to', $this->arrayOfObject()),

            V::keyOptional('file_ids', $this->arrayOfString()),
            V::keyOptional('subject', V::stringType()),
            V::keyOptional('body', V::stringType()),

            V::key('access_token', V::stringType()->notEmpty())
        ];
    }

    // ------------------------------------------------------------------------------

}