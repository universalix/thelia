<?php
/*************************************************************************************/
/*                                                                                   */
/*      Thelia	                                                                     */
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
/*	    along with this program. If not, see <http://www.gnu.org/licenses/>.         */
/*                                                                                   */
/*************************************************************************************/

namespace Thelia\Core\Event;

use Propel\Runtime\ActiveQuery\ModelCriteria;

class UpdateFilePositionEvent extends UpdatePositionEvent
{
    protected $query;

    /**
     * @param ModelCriteria $query
     * @param               $object_id
     * @param null          $mode
     * @param null          $position
     */
    public function __construct(ModelCriteria $query, $object_id, $mode, $position = null)
    {
        parent::__construct($object_id, $mode, $position);

        $this->setQuery($query);
    }

    /**
     * @param ModelCriteria $query
     */
    public function setQuery(ModelCriteria $query)
    {
        $this->query = $query;
    }

    /**
     * @return ModelCriteria|null
     */
    public function getQuery()
    {
        return $this->query;
    }
}
