<?php
/*
 * This file is part of the SocialNetworkBundle package.
 *
 * (c) Fulgurio <http://fulgurio.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fulgurio\SocialNetworkBundle\Form\Handler\MessengerList;

use Fulgurio\SocialNetworkBundle\Form\Handler\AbstractAjaxForm;
use Doctrine\Bundle\DoctrineBundle\Registry;

class NewListFormHandler extends AbstractAjaxForm
{
    /**
     * Processing form values
     *
     * @param Doctrine\Bundle\DoctrineBundle\Registry $doctrine
     * @return boolean
     */
    public function process(Registry $doctrine)
    {
        if ($this->request->getMethod() == 'POST')
        {
            $this->form->handleRequest($this->request);
            if ($this->form->isValid())
            {
                $group = $this->form->getData();
                $em = $doctrine->getManager();
                $em->persist($group);
                $em->flush();
                return TRUE;
            }
            else
            {
                $this->hasErrors = TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Translate message
     *
     * @param string $message
     * @return string
     */
    protected function translate($message)
    {
        return $this->translator->trans($message, array(), 'messenger-list');
    }
}