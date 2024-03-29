<?php

/**
 * Model for season tracking
 *
 */

class SeasonTracking extends BaseEntity  {
	
	public function setTableDefinition() {
		#add the table definitions from the parent table
		parent::setTableDefinition();
		
		// set the table
		$this->setTableName('seasontracking');
		$this->hasColumn('seasonid', 'integer', null, array('notblank' => true));	
		$this->hasColumn('userid', 'integer', null, array('default' => NULL));
		$this->hasColumn('cropid', 'integer', null);
		$this->hasColumn('type', 'integer', null, array('notblank' => true)); // 1 - Herbicide, 2 - Insectcide, 3 - Fungicide, 4 - Fertiliser, 5 - Manure, 6 - Irrigation
		$this->hasColumn('ref', 'string', 50);
		$this->hasColumn('activityname', 'string', 255);
		$this->hasColumn('itemname', 'string', 255);
		$this->hasColumn('itemtype', 'integer', null);
		$this->hasColumn('itemform', 'integer', null);
		$this->hasColumn('itemstorage', 'integer', null);
		$this->hasColumn('startdate','date', null, array('notblank' => true));
		$this->hasColumn('enddate','date', null);
		$this->hasColumn('method', 'integer', null, array('notblank' => true));
		$this->hasColumn('timing', 'integer', null);
		$this->hasColumn('status', 'integer', null);
		$this->hasColumn('itemrate', 'decimal', 10, array('default' => NULL));
		$this->hasColumn('itemrateunit', 'integer', null);
		$this->hasColumn('totalapplied', 'decimal', 10, array('default' => NULL));
		$this->hasColumn('totalappliedunit', 'integer', null);
		$this->hasColumn('totalexpenses', 'decimal', 10, array('default' => NULL));
		$this->hasColumn('financetype', 'integer', null, array('default' => '1'));
		$this->hasColumn('target','string', 500);
		$this->hasColumn('additives','string', 500);
		$this->hasColumn('notes','string', 1000);
	}
	
	/**
	 * Contructor method for custom functionality - add the fields to be marked as dates
	 */
	public function construct() {
		parent::construct();
		
		$this->addDateFields(array("startdate","enddate"));
		
		// set the custom error messages
       	$this->addCustomErrorMessages(array(
       									"seasonid.notblank" => $this->translate->_("season_seasonid_error"),
       									"type.notblank" => $this->translate->_("season_trackingtype_error"),
       									"startdate.notblank" => $this->translate->_("season_activitydate_error"),
       									"method.notblank" => $this->translate->_("season_method_error")
       	       						));
	}
	public function setUp() {
		parent::setUp();
		
		$this->hasOne('Season as season',
							array('local' => 'seasonid',
									'foreign' => 'id'
							)
						);
		$this->hasOne('UserAccount as user',
							array('local' => 'userid',
									'foreign' => 'id'
							)
						);
		$this->hasOne('Commodity as crop',
							array('local' => 'cropid',
									'foreign' => 'id'
							)
						);
		$this->hasMany('Loan as activitycredit',
					 		array(
								'local' => 'id',
								'foreign' => 'trackingid'
							)
						);
		$this->hasMany('SeasonLabour as labourdetails',
					 		array(
								'local' => 'id',
								'foreign' => 'trackingid'
							)
						);
	}
	/*
	 * Pre process model data
	 */
	function processPost($formvalues) {
		// trim spaces from the name field
		if(isArrayKeyAnEmptyString('cropid', $formvalues)){
			$formvalues['cropid'] = NULL;
		}
		if(isArrayKeyAnEmptyString('userid', $formvalues)){
			$formvalues['userid'] = NULL;
		}
		if(isArrayKeyAnEmptyString('status', $formvalues)){
			$formvalues['status'] = NULL;
		}
		if(isArrayKeyAnEmptyString('enddate', $formvalues)){
			$formvalues['enddate'] = NULL;
		}
		if(isArrayKeyAnEmptyString('itemrate', $formvalues)){
			$formvalues['itemrate'] = NULL;
		}
		if(isArrayKeyAnEmptyString('itemtype', $formvalues)){
			$formvalues['itemtype'] = NULL;
		}
		if(isArrayKeyAnEmptyString('itemform', $formvalues)){
			$formvalues['itemform'] = NULL;
		}
		if(isArrayKeyAnEmptyString('itemstorage', $formvalues)){
			$formvalues['itemstorage'] = NULL;
		}
		if(isArrayKeyAnEmptyString('itemrateunit', $formvalues)){
			$formvalues['itemrateunit'] = NULL;
		}
		if(isArrayKeyAnEmptyString('totalapplied', $formvalues)){
			$formvalues['totalapplied'] = NULL;
		}
		if(isArrayKeyAnEmptyString('totalappliedunit', $formvalues)){
			$formvalues['totalappliedunit'] = NULL;
		}
		if(isArrayKeyAnEmptyString('totalexpenses', $formvalues)){
			$formvalues['totalexpenses'] = 0;
		}
		if(isArrayKeyAnEmptyString('timing', $formvalues)){
			$formvalues['timing'] = NULL;
		}
		
		if(!isArrayKeyAnEmptyString('financetype', $formvalues)){
			if($formvalues['financetype'] == 3 || $formvalues['financetype'] == 4 || $formvalues['financetype'] == 5){
				$formvalues['activitycredit'][0]['financetype'] = $formvalues['financetype'];
				$formvalues['activitycredit'][0]['userid'] = $formvalues['userid'];
				$formvalues['activitycredit'][0]['stage'] = $formvalues['stage'];
				$formvalues['activitycredit'][0]['type'] = $formvalues['type'];
				isArrayKeyAnEmptyString('principal', $formvalues) ? $formvalues['activitycredit'][0]['principal'] = NULL : $formvalues['activitycredit'][0]['principal'] = $formvalues['principal'];
				isArrayKeyAnEmptyString('interestrate', $formvalues) ? $formvalues['activitycredit'][0]['interestrate'] = NULL : $formvalues['activitycredit'][0]['interestrate'] = $formvalues['interestrate'];
				isArrayKeyAnEmptyString('paybackamount', $formvalues) ? $formvalues['activitycredit'][0]['paybackamount'] = NULL : $formvalues['activitycredit'][0]['paybackamount'] = $formvalues['paybackamount'];
				isArrayKeyAnEmptyString('installment', $formvalues) ? $formvalues['activitycredit'][0]['installment'] = NULL : $formvalues['activitycredit'][0]['installment'] = $formvalues['installment'];
				isArrayKeyAnEmptyString('installmentunit', $formvalues) ? $formvalues['activitycredit'][0]['installmentunit'] = NULL : $formvalues['activitycredit'][0]['installmentunit'] = $formvalues['installmentunit'];
				isArrayKeyAnEmptyString('paybackperiod', $formvalues) ? $formvalues['activitycredit'][0]['paybackperiod'] = NULL : $formvalues['activitycredit'][0]['paybackperiod'] = $formvalues['paybackperiod'];
				isArrayKeyAnEmptyString('paybackperiodunit', $formvalues) ? $formvalues['activitycredit'][0]['paybackperiodunit'] = NULL : $formvalues['activitycredit'][0]['paybackperiodunit'] = $formvalues['paybackperiodunit'];
				isArrayKeyAnEmptyString('creditdate', $formvalues) ? $formvalues['activitycredit'][0]['creditdate'] = NULL : $formvalues['activitycredit'][0]['creditdate'] = changeDateFromPageToMySQLFormat($formvalues['creditdate']);
				isArrayKeyAnEmptyString('financesourceid', $formvalues) ? $formvalues['activitycredit'][0]['financesourceid'] = NULL : $formvalues['activitycredit'][0]['financesourceid'] = $formvalues['financesourceid'];
				isArrayKeyAnEmptyString('financesourcetext', $formvalues) ? $formvalues['activitycredit'][0]['financesourcetext'] = NULL : $formvalues['activitycredit'][0]['financesourcetext'] = $formvalues['financesourcetext'];
				isArrayKeyAnEmptyString('clientid', $formvalues) ? $formvalues['activitycredit'][0]['clientid'] = NULL : $formvalues['activitycredit'][0]['clientid'] = $formvalues['clientid'];
				isArrayKeyAnEmptyString('quantity', $formvalues) ? $formvalues['activitycredit'][0]['quantity'] = NULL : $formvalues['activitycredit'][0]['quantity'] = $formvalues['quantity'];
				isArrayKeyAnEmptyString('quantityunit', $formvalues) ? $formvalues['activitycredit'][0]['quantityunit'] = NULL : $formvalues['activitycredit'][0]['quantityunit'] = $formvalues['quantityunit'];
				isArrayKeyAnEmptyString('price', $formvalues) ? $formvalues['activitycredit'][0]['price'] = NULL : $formvalues['activitycredit'][0]['price'] = $formvalues['price'];
				isArrayKeyAnEmptyString('clienttype', $formvalues) ? $formvalues['activitycredit'][0]['clienttype'] = NULL : $formvalues['activitycredit'][0]['clienttype'] = $formvalues['clienttype'];
				isArrayKeyAnEmptyString('sourcetype', $formvalues) ? $formvalues['activitycredit'][0]['sourcetype'] = NULL : $formvalues['activitycredit'][0]['sourcetype'] = $formvalues['sourcetype'];
				isArrayKeyAnEmptyString('contract', $formvalues) ? $formvalues['activitycredit'][0]['contract'] = NULL : $formvalues['activitycredit'][0]['contract'] = $formvalues['contract'];
			} else {
				$formvalues['activitycredit'] = array();
			}
		}
		
		# process season labour details
		$detailsarray = array();
		if(!isArrayKeyAnEmptyString('labourdetails', $formvalues)){
			foreach ($formvalues['labourdetails'] as $key => $value){
				if(isEmptyString($value['activityname'])){
					unset($value[$key]);
				} else {
					$countfields = array('itempaid','fieldsize','fieldsizeunit');
					foreach ($value as $thekey => $thevalue){
						if(isEmptyString($thevalue)){
							if(in_array($thekey, $countfields)){
								$value[$thekey] = NULL;
							} else {
								$value[$thekey] = 0;
							}
						}
					}
					$detailsarray[$key] = $value;
					if(!isArrayKeyAnEmptyString('id', $value)){
						$detailsarray[$key]['id'] = $value['id'];
					}
					$detailsarray[$key]['userid'] = $formvalues['userid'];
					$detailsarray[$key]['seasonid'] = $formvalues['seasonid'];
					if(!isArrayKeyAnEmptyString('labourdetails_fieldsizeunit_'.$key, $formvalues)){
						$detailsarray[$key]['fieldsizeunit'] = $formvalues['labourdetails_fieldsizeunit_'.$key];
					} else {
						$detailsarray[$key]['fieldsizeunit'] = NULL;
					}
				}
			}
			// unset($formvalues['labourdetails']);
		}
		if(count($detailsarray) > 0){
			$formvalues['labourdetails'] = $detailsarray;
		} else {
			$formvalues['labourdetails'] = array();
		}
		// debugMessage($formvalues); // exit();
		parent::processPost($formvalues);
	}
	# determine method display text
    function getMethodText() {
    	$text = '--';
    	if(!isEmptyString($this->getMethod())){
    		$values = getTreatmentMethods();
    		$text = $values[$this->getMethod()];
    	}
    	return $text;
    }
	# determine current status label
    function getStatusText() {
    	$text = '--';
    	if(!isEmptyString($this->getStatus())){
    		$values = getStatusValues();
    		$text = $values[$this->getStatus()];
    	}
    	return $text;
    }
	# determine item rate units
    function getItemRateUnitText() {
    	$text = '--';
    	if(!isEmptyString($this->getItemRateUnit())){
    		$values = getTreatmentMeasureUnits();
    		$text = $values[$this->getItemRateUnit()];
    	}
    	return $text;
    }
	# determine item quantity units
    function getTotalAppliedUnitText() {
    	$text = '--';
    	if(!isEmptyString($this->getTotalAppliedUnit())){
    		$values = getTreatmentTotalUnits();
    		$text = $values[$this->getTotalAppliedUnit()];
    	}
    	return $text;
    }
	# determine item type label
    function getItemTypeText() {
    	$text = '--';
    	if(!isEmptyString($this->getItemType())){
    		$values = getTreatmentSubTypes();
    		$text = $values[$this->getItemType()];
    	}
    	return $text;
    }
	# determine item type label
    function getItemFormText() {
    	$text = '--';
    	if(!isEmptyString($this->getItemForm())){
    		$values = getTreatmentForms();
    		$text = $values[$this->getItemForm()];
    	}
    	return $text;
    }
	# determine timing display text
    function getTimingText() {
    	$text = '--';
    	if(!isEmptyString($this->getTiming())){
    		$values = getTimingValues();
    		$text = $values[$this->getTiming()];
    	}
    	return $text;
    }
	# determine timing display text
    function getTypeName() {
    	$text = '--';
    	if(!isEmptyString($this->getType())){
    		$values = getTreatmentTypes();
    		$text = $values[$this->getType()];
    	}
    	return $text;
    }
	# generate next activity reference counter
    function getNextReferencePointer() {
    	$conn = Doctrine_Manager::connection();
    	$session = SessionWrapper::getInstance();
    	$userid = $session->getVar('userid');
		$sql = "SELECT COUNT(id) FROM seasontracking WHERE seasonid = ".$this->getSeasonID()." "; 
		$result = $conn->fetchOne($sql);
		return str_pad(($result+1), 3, "0", STR_PAD_LEFT);
    }
	# determine all expenses for entry
	function getExpensesDetails() {
    	$q = Doctrine_Query::create()->from('SeasonInputDetail s')->where("s.trackingid = '".$this->getID()."'")->orderby('s.inputdate DESC');
		$result = $q->execute();
		return $result;
	}
	# determine total expenses for the expense
	function getActivityExpenseAmount(){
		$expenselines = $this->getExpensesDetails();
		$expensetotal = 0;
		if($expenselines){
			foreach ($expenselines as $expense){
				$expensetotal += isEmptyString($expense->getAmount()) ? 0 : $expense->getAmount();
			}
		}
		return $expensetotal;
	}
	# determine all notes for entry
	function getNotesDetails() {
    	$q = Doctrine_Query::create()->from('Notes n')->where("n.trackingid = '".$this->getID()."'")->orderby('n.datenoted DESC');
		$result = $q->execute();
		return $result;
	}
	# determine credit history for activity
	function getCreditDetails() {
    	$q = Doctrine_Query::create()->from('Loan l')->where("l.trackingid = '".$this->getID()."'");
		$result = $q->execute();
		return $result->get(0);
	}
	# determine the family labour history
	function getFamilyLabourDetails() {
    	$q = Doctrine_Query::create()->from('SeasonLabour l')->where("l.trackingid = '".$this->getID()."' AND l.type = 1 ");
		$result = $q->execute();
		return $result;
	}
	# determine the hired labour history
	function getHiredLabourDetails() {
    	$q = Doctrine_Query::create()->from('SeasonLabour l')->where("l.trackingid = '".$this->getID()."' AND l.type = 2 ");
		$result = $q->execute();
		return $result;
	}
	# determine the total labor cost
	function getTotalLaborCost() {
		$labourdetails = $this->getHiredLabourDetails();
		$sumamount = 0;
		foreach($labourdetails as $labour){
			$sumamount += $labour->getamount();
		}
		return $sumamount;
	}
}

?>