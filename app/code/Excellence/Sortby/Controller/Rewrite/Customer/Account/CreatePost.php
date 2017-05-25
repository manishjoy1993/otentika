<?php
namespace Excellence\Sortby\Controller\Rewrite\Customer\Account;

class CreatePost extends \Magento\Customer\Controller\Account\CreatePost
{
    /**
     * Make sure that password and password confirmation matched
     *
     * @param string $password
     * @param string $confirmation
     * @return void
     * @throws InputException
     */
    protected function checkPasswordConfirmation($password, $confirmation)
    {
        // if ($password != $confirmation) {
        //     throw new InputException(__('Please make sure your passwords match.'));
        // }
        // Disable Confirm Passowrd
        return true;
    }
}
