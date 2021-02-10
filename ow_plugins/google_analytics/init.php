<?php

/**
 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.

 * ---
 * Copyright (c) 2011, Oxwall Foundation
 * All rights reserved.

 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the
 * following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice, this list of conditions and
 *  the following disclaimer.
 *
 *  - Redistributions in binary form must reproduce the above copyright notice, this list of conditions and
 *  the following disclaimer in the documentation and/or other materials provided with the distribution.
 *
 *  - Neither the name of the Oxwall Foundation nor the names of its contributors may be used to endorse or promote products
 *  derived from this software without specific prior written permission.

 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
$webPropertyId = OW::getConfig()->getValue('ganalytics', 'web_property_id');

if ( $webPropertyId !== null )
{

    function ganalytics_add_code()
    {

        $code = '<script type="text/javascript">
            var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
            document.write(unescape("%3Cscript src=\'" + gaJsHost + "google-analytics.com/ga.js\' type=\'text/javascript\'%3E%3C/script%3E"));
            </script>
            <script type="text/javascript">
            try {
                var pageTracker = _gat._getTracker("' . trim(OW::getConfig()->getValue('ganalytics', 'web_property_id')) . '");
                pageTracker._trackPageview();
            } catch(err) {}
            </script>';

        OW::getDocument()->appendBody($code);
    }
    OW::getEventManager()->bind(OW_EventManager::ON_FINALIZE, 'ganalytics_add_code');
}

OW::getRouter()->addRoute(new OW_Route('ganalytics_admin', 'admin/plugins/ganalytics', 'GANALYTICS_CTRL_Admin', 'index'));

function ganalytics_admin_notification( BASE_CLASS_EventCollector $event )
{
    $wpid = OW::getConfig()->getValue('ganalytics', 'web_property_id');

    if ( empty($wpid) )
    {
        $event->add(OW::getLanguage()->text('ganalytics', 'admin_notification_text', array('link' => OW::getRouter()->urlForRoute('ganalytics_admin'))));
    }
}
OW::getEventManager()->bind('admin.add_admin_notification', 'ganalytics_admin_notification');
