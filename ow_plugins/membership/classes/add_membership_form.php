<?php

/**
 * Add membership form
 *
 * @author Sergey Pryadkin <GiperProger@gmail.com>
 * @package ow.ow_plugins.membership.classes
 * @since 1.8.4
 */

class MEMBERSHIP_CLASS_AddMembershipForm extends Form
{
    protected $assignedMemberships;

    public function __construct($assignedMemberships)
    {
        parent::__construct('add-membership-form');

        $this->assignedMemberships = $assignedMemberships;
        $lang = OW::getLanguage();

        $accTypeField = new HiddenField('accType');
        $accTypeField->setRequired(true);
        $this->addElement($accTypeField);

        $rolesField = new Selectbox('role');
        $roles = MEMBERSHIP_BOL_MembershipService::
        getInstance()->getRolesAvailableForMembership($this->assignedMemberships);

        $options = array();

        foreach ( $roles as $role )
        {
            $options[$role->id] = $lang->text('base', 'authorization_role_' . $role->name);
        }
        if ( count($options) )
        {
            $rolesField->setOptions($options);
        }
        $rolesField
            ->setRequired(true)
            ->setLabel($lang->text('membership', 'select_role'));

        $this->addElement($rolesField);

        $periodField = new TextField('period');
        $periodField->setRequired(true);
        $periodField->addValidator(new IntValidator(1, 100000));
        $this->addElement($periodField);

        $periodUnits= new Selectbox('periodUnits');
        $periodUnits->setRequired(true);
        $periodUnits->setOptions(array(
            MEMBERSHIP_BOL_MembershipService::PERIOD_DAYS => OW::getLanguage()->text('membership', 'days'),
            MEMBERSHIP_BOL_MembershipService::PERIOD_MONTHS => OW::getLanguage()->text('membership', 'months')
        ));
        $periodUnits->setHasInvitation(false);
        $this->addElement($periodUnits);

        $priceField = new TextField('price');
        $priceField->setRequired(true);
        $priceField->addValidator(new FloatValidator(0, 1000000));
        $this->addElement($priceField);

        $recurringField = new CheckboxField('isRecurring');
        $this->addElement($recurringField);

        // submit
        $submit = new Submit('save');
        $submit->setValue($lang->text('membership', 'add_btn'));
        $this->addElement($submit);
    }

    public function process()
    {
        $values = $this->getValues();

        $type = new MEMBERSHIP_BOL_MembershipType();
        $type->roleId = $values['role'];
        $type->accountTypeId = $values['accType'];

        if ( isset($values['price']) && isset($values['period']) )
        {
            $plan = new MEMBERSHIP_BOL_MembershipPlan();
            $plan->price = floatval($values['price']);
            $plan->period = intval($values['period']);

            $periodUnitsList = MEMBERSHIP_BOL_MembershipService::getInstance()->getPeriodUnitsList();

            $plan->periodUnits = !empty($values['periodUnits']) && in_array($values['periodUnits'], $periodUnitsList) ? $values['periodUnits'] : $periodUnitsList[0];
            $plan->recurring = isset($values['isRecurring']) && $values['price'] > 0 ? $values['isRecurring'] : false;
        }
        else
        {
            $plan = null;
        }

        $res = MEMBERSHIP_BOL_MembershipService::getInstance()->addType($type, $plan);

        return $res;
    }
}