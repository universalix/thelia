<?php
/**********************************************************************************/
/*                                                                                */
/*      Thelia	                                                                  */
/*                                                                                */
/*      Copyright (c) OpenStudio                                                  */
/*      email : info@thelia.net                                                   */
/*      web : http://www.thelia.net                                               */
/*                                                                                */
/*      This program is free software; you can redistribute it and/or modify      */
/*      it under the terms of the GNU General Public License as published by      */
/*      the Free Software Foundation; either version 3 of the License             */
/*                                                                                */
/*      This program is distributed in the hope that it will be useful,           */
/*      but WITHOUT ANY WARRANTY; without even the implied warranty of            */
/*      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             */
/*      GNU General Public License for more details.                              */
/*                                                                                */
/*      You should have received a copy of the GNU General Public License         */
/*	    along with this program. If not, see <http://www.gnu.org/licenses/>.      */
/*                                                                                */
/**********************************************************************************/

namespace Thelia\Coupon\Type;

use Thelia\Coupon\FacadeInterface;

/**
 * @package Coupon
 * @author  Guillaume MOREL <gmorel@openstudio.fr>
 *
 */
class RemoveXPercent extends CouponAbstract
{
    const INPUT_PERCENTAGE_NAME = 'percentage';

    /** @var string Service Id  */
    protected $serviceId = 'thelia.coupon.type.remove_x_percent';

    /** @var float Percentage removed from the Cart */
    protected $percentage = 0;

    /** @var array Extended Inputs to manage */
    protected $extendedInputs = array(
        self::INPUT_PERCENTAGE_NAME
    );

    /**
     * Set Coupon
     *
     * @param FacadeInterface $facade                     Provides necessary value from Thelia
     * @param string          $code                       Coupon code (ex: XMAS)
     * @param string          $title                      Coupon title (ex: Coupon for XMAS)
     * @param string          $shortDescription           Coupon short description
     * @param string          $description                Coupon description
     * @param array           $effects                    Coupon effects params
     * @param bool            $isCumulative               If Coupon is cumulative
     * @param bool            $isRemovingPostage          If Coupon is removing postage
     * @param bool            $isAvailableOnSpecialOffers If available on Product already
     *                                                    on special offer price
     * @param bool            $isEnabled                  False if Coupon is disabled by admin
     * @param int             $maxUsage                   How many usage left
     * @param \Datetime       $expirationDate             When the Code is expiring
     *
     * @return $this
     */
    public function set(
        FacadeInterface $facade,
        $code,
        $title,
        $shortDescription,
        $description,
        array $effects,
        $isCumulative,
        $isRemovingPostage,
        $isAvailableOnSpecialOffers,
        $isEnabled,
        $maxUsage,
        \DateTime $expirationDate
    )
    {
        parent::set(
            $facade, $code, $title, $shortDescription, $description, $effects, $isCumulative, $isRemovingPostage, $isAvailableOnSpecialOffers, $isEnabled, $maxUsage, $expirationDate
        );
        $this->percentage = $effects[self::INPUT_PERCENTAGE_NAME];

        return $this;
    }

    /**
     * Return effects generated by the coupon
     * A negative value
     *
     * @throws \Thelia\Exception\MissingFacadeException
     * @throws \InvalidArgumentException
     * @return float
     */
    public function exec()
    {
        if ($this->percentage >= 100) {
            throw new \InvalidArgumentException(
                'Percentage must be inferior to 100'
            );
        }

        return round($this->facade->getCartTotalTaxPrice() *  $this->percentage/100, 2);
    }

    /**
     * Get I18n name
     *
     * @return string
     */
    public function getName()
    {
        return $this->facade
            ->getTranslator()
            ->trans('Remove X percent to total cart', array(), 'coupon');
    }

    /**
     * Get I18n amount input name
     *
     * @return string
     */
    public function getInputName()
    {
        return $this->facade
            ->getTranslator()
            ->trans('Percentage removed from the cart', array(), 'coupon');
    }

    /**
     * Get I18n tooltip
     *
     * @return string
     */
    public function getToolTip()
    {
        $toolTip = $this->facade
            ->getTranslator()
            ->trans(
                'This coupon will remove the entered percentage to the customer total checkout. If the discount is superior to the total checkout price the customer will only pay the postage. Unless if the coupon is set to remove postage too.',
                array(),
                'coupon'
            );

        return $toolTip;
    }

    /**
     * Draw the input displayed in the BackOffice
     * allowing Admin to set its Coupon effect
     *
     * @return string HTML string
     */
    public function drawBackOfficeInputs()
    {
        $labelPercentage = $this->getInputName();

        $html = '
                <input type="hidden" name="thelia_coupon_creation[' . self::INPUT_AMOUNT_NAME . ']" value="0"/>
                <div class="form-group input-' . self::INPUT_PERCENTAGE_NAME . '">
                    <label for="' . self::INPUT_PERCENTAGE_NAME . '" class="control-label">' . $labelPercentage . '</label>
                    <input id="' . self::INPUT_PERCENTAGE_NAME . '" class="form-control" name="' . self::INPUT_EXTENDED__NAME . '[' . self::INPUT_PERCENTAGE_NAME . ']' . '" type="text" value="' . $this->percentage . '"/>
                </div>
            ';

        return $html;
    }

}
