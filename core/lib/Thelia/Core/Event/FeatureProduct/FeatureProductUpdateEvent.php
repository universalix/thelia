<?php
/*************************************************************************************/
/*                                                                                   */
/*      Thelia                                                                       */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : info@thelia.net                                                      */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      This program is free software; you can redistribute it and/or modify         */
/*      it under the terms of the GNU General Public License as published by         */
/*      the Free Software Foundation; either version 3 of the License                */
/*                                                                                   */
/*      This program is distributed in the hope that it will be useful,              */
/*      but WITHOUT ANY WARRANTY; without even the implied warranty of               */
/*      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                */
/*      GNU General Public License for more details.                                 */
/*                                                                                   */
/*      You should have received a copy of the GNU General Public License            */
/*      along with this program. If not, see <http://www.gnu.org/licenses/>.         */
/*                                                                                   */
/*************************************************************************************/

namespace Thelia\Core\Event\FeatureProduct;

class FeatureProductUpdateEvent extends FeatureProductEvent
{
    protected $product_id;
    protected $feature_id;
    protected $feature_value;
    protected $is_text_value;

    public function __construct($product_id, $feature_id, $feature_value, $is_text_value = false)
    {
        $this->product_id = $product_id;
        $this->feature_id = $feature_id;
        $this->feature_value = $feature_value;
        $this->is_text_value = $is_text_value;
    }

    public function getProductId()
    {
        return $this->product_id;
    }

    public function setProductId($product_id)
    {
        $this->product_id = $product_id;

        return $this;
    }

    public function getFeatureId()
    {
        return $this->feature_id;
    }

    public function setFeatureId($feature_id)
    {
        $this->feature_id = $feature_id;

        return $this;
    }

    public function getFeatureValue()
    {
        return $this->feature_value;
    }

    public function setFeatureValue($feature_value)
    {
        $this->feature_value = $feature_value;

        return $this;
    }

    public function getIsTextValue()
    {
        return $this->is_text_value;
    }

    public function setIsTextValue($is_text_value)
    {
        $this->is_text_value = $is_text_value;

        return $this;
    }
}
