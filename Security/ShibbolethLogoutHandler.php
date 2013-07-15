<?php
/**
 * This file is part of kuleuven/shibboleth-bundle
 *
 * kuleuven/shibboleth-bundle is free software; you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 *
 * kuleuven/shibboleth-bundle is distributed in the hope that it will be
 * useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with kuleuven/shibboleth-bundle; if not, see
 * <http://www.gnu.org/licenses/>.
 *
 * Copyright (C) 2013 Ronny Moreas, KU Leuven
 *
 * @package     kuleuven/shibboleth-bundle
 * @copyright   (C) 2013 Ronny Moreas, KU Leuven
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL-3
 */
 namespace KULeuven\ShibbolethBundle\Security;

use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use KULeuven\ShibbolethBundle\Service\Shibboleth;

class ShibbolethLogoutHandler implements LogoutHandlerInterface, LogoutSuccessHandlerInterface
{
    private $shibboleth;
    private $httpUtils;
    private $target;

    public function __construct(Shibboleth $shibboleth, HttpUtils $httpUtils, $targetUrl = '/')
    {
        $this->shibboleth = $shibboleth;
        $this->httpUtils = $httpUtils;
        $this->target = $targetUrl;
    }

    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        if ($token instanceof ShibbolethUserToken) {
            $request->getSession()->invalidate();
        }
    }

    public function onLogoutSuccess(Request $request)
    {
        // set the logout message to flashbag if there is any
        if ($this->shibboleth->getLogoutMessage()) {
            $request->getSession()->getFlashBag()->add(
                'notice',
                $this->shibboleth->getLogoutMessage()
            );
        }
        $returnUri = $this->httpUtils->generateUri($request, $this->target);
        $response = new RedirectResponse($this->shibboleth->getLogoutUrl($request, $returnUri));
        return $response;
    }
}
