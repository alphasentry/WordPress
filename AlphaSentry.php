<?php 
/**
 * @author AlphaSentry Development Team
 * 
 * This is the AlphaSentry PHP5 helper client. For full API documentation visit:
 * 
 * http://www.alphasentry.com/docs/
 *
 * Copyright 2010-2012, AlphaSentry.com.  All Rights Reserved.
 * 
 */
class AlphaSentry
{
	
	/**
	 * Your AlphaSentry API Key from your registered account at AlphaSentry.com
	 * 
	 * @var string
	 */
	private $ApiKey = '';
	
	/**
	 * Sentry API SOAP Client
	 *
	 * @var SoapClient
	 */
	public $Client = null; 
	
	/**
	 * GreyList API SOAP Client
	 *
	 * @var SoapClient
	 */
	public $GreyListClient = null;
	
	/**
	 * Array containing the Sentry API response.
	 *
	 * @var array
	 */
	public $Response = array('Success' => false, 'Errors' => array('Query never executed')); 

	/**
	 * Array containing the GeoCode response.
	 *
	 * @var array
	 */
	public $GeocodeResponse = array('Success' => false, 'Errors' => array('Query never executed'));
	
	/**
	 * Your API key for the Yahoo GeoCoding API. Get your key here:
	 * http://developer.yahoo.com/geo/placefinder/
	 *
	 * @var string
	 */
	private $GeocodeYahooApiKey = '';
	
	/**
	 * Array containing the GreyList API response.
	 *
	 * @var array
	 */
	public $GreyListResponse = array('Success' => false, 'Errors' => array('Query never executed')); 
	
	
	/**
	 * Constructor for the AlphaSentry client object
	 * 
	 * @param string $ApiKey
	 */
	public function __construct ($ApiKey = '') 
	{
		try
		{
			// Use the API Key provided in this file if one is not provided to the constructor.
			if(strlen($ApiKey) > 0)
				$this->ApiKey = $ApiKey;
			
			// Create the Sentry API SOAP Client from the WSDL
			$this->Client = new SoapClient('http://api.alphasentry.com/api/alphasentry/1/AlphaSentry.wsdl', array('location' => 'https://api.alphasentry.com/api/alphasentry/1/', 'soap_version' => SOAP_1_2, 'trace' => 1, 'exceptions' => 0));
			// Create the GreyList API SOAP Client from the WSDL
			$this->GreyListClient = new SoapClient('http://api.alphasentry.com/api/greylist/1/GreyList.wsdl', array('location' => 'https://api.alphasentry.com/api/greylist/1/', 'soap_version' => SOAP_1_2, 'trace' => 1, 'exceptions' => 0));
			return;
		}
		catch(Exception $e)
		{
			return;
		}
	}
	
	/**
	 * Track and assess a visitor login
	 * 
	 * @param string $ASVar1
	 * @param string $ASVar2
	 * @param string $UserId
	 * @param string $UserVar1
	 * @param string $UserVar2
	 * @return boolean
	 */
	public function TrackLogin ($ASVar1, $ASVar2, $UserId, $UserVar1 = '', $UserVar2 = '')
	{
		$this->ResetResponse();
		try
		{
			$request = new AlphaSentryTrackRequest();
			$request->ApiKey = $this->ApiKey;
			$request->ASVar1 = $ASVar1;
			$request->ASVar2 = $ASVar2;
			$request->ServerVars = base64_encode(http_build_query($_SERVER));
			$request->UserId = $UserId;
			$request->UserVar1 = $UserVar1;
			$request->UserVar2 = $UserVar2;
			
			$response = $this->Client->__soapCall('TrackLogin', array($request));
			$this->Response = get_object_vars($response);
			if($this->Response['Success'])
				return true;
			else
				return false;
		}
		catch(Exception $e)
		{
			return false;
		}
	}
	
	
	/**
	 * Track and assess a new visitor account
	 * 
	 * @param string $ASVar1
	 * @param string $ASVar2
	 * @param string $UserId
	 * @param string $UserVar1
	 * @param string $UserVar2
	 * @param string $UserCountryCode
	 * @param double $UserLatitude
	 * @param double $UserLongitude
	 * @return boolean
	 */
	public function TrackAccount ($ASVar1, $ASVar2, $UserId, $UserVar1 = '', $UserVar2 = '', $UserCountryCode = '', $UserLatitude = '', $UserLongitude = '')
	{
		$this->ResetResponse();
		try
		{
			$request = new AlphaSentryTrackRequest();
			$request->ApiKey = $this->ApiKey;
			$request->ASVar1 = $ASVar1;
			$request->ASVar2 = $ASVar2;
			$request->ServerVars = base64_encode(http_build_query($_SERVER));
			$request->UserId = $UserId;
			$request->UserVar1 = $UserVar1;
			$request->UserVar2 = $UserVar2;
			$request->UserCountryCode = $UserCountryCode;
			$request->UserLatitude = $UserLatitude;
			$request->UserLongitude = $UserLongitude;
			
			$response = $this->Client->__soapCall('TrackAccount', array($request));
			$this->Response = get_object_vars($response);
			if($this->Response['Success'])
				return true;
			else
				return false;
		}
		catch(Exception $e)
		{
			return false;
		}
	}
	
	
	/**
	 * Track and assess a new visitor purchase
	 * 
	 * @param unknown_type $ASVar1
	 * @param unknown_type $ASVar2
	 * @param unknown_type $PurchaseId
	 * @param unknown_type $UserId
	 * @param unknown_type $UserVar1
	 * @param unknown_type $UserVar2
	 * @param unknown_type $UserCountryCode
	 * @param unknown_type $UserLatitude
	 * @param unknown_type $UserLongitude
	 * @param unknown_type $PurchasePostalCode
	 * @param unknown_type $PurchaseStreetAddress
	 * @return boolean
	 */
	public function TrackPurchase ($ASVar1, $ASVar2, $PurchaseId, $UserId = '', $UserVar1 = '', $UserVar2 = '', $UserCountryCode = '', $UserLatitude = '', $UserLongitude = '', $PurchasePostalCode = '', $PurchaseStreetAddress = '')
	{
		$this->ResetResponse();
		try
		{
			$request = new AlphaSentryTrackRequest();
			$request->ApiKey = $this->ApiKey;
			$request->ASVar1 = $ASVar1;
			$request->ASVar2 = $ASVar2;
			$request->ServerVars = base64_encode(http_build_query($_SERVER));
			$request->PurchaseId = $PurchaseId;
			$request->UserId = $UserId;
			$request->UserVar1 = $UserVar1;
			$request->UserVar2 = $UserVar2;
			$request->UserCountryCode = $UserCountryCode;
			$request->UserLatitude = $UserLatitude;
			$request->UserLongitude = $UserLongitude;
			$request->PurchasePostalCode = $PurchasePostalCode;
			$request->PurchaseStreetAddress = $PurchaseStreetAddress;
			
			$response = $this->Client->__soapCall('TrackPurchase', array($request));
			$this->Response = get_object_vars($response);
			if($this->Response['Success'])
				return true;
			else
				return false;
		}
		catch(Exception $e)
		{
			return false;
		}
	}
	
	
	/**
	 * Track and assess a user action
	 * 
	 * @param unknown_type $ASVar1
	 * @param unknown_type $ASVar2
	 * @param unknown_type $UserId
	 * @param unknown_type $UserVar1
	 * @param unknown_type $UserVar2
	 * @return boolean
	 */
	public function TrackOther ($ASVar1, $ASVar2, $UserId = '', $UserVar1 = '', $UserVar2 = '')
	{
		$this->ResetResponse();
		try
		{
			$request = new AlphaSentryTrackRequest();
			$request->ApiKey = $this->ApiKey;
			$request->ASVar1 = $ASVar1;
			$request->ASVar2 = $ASVar2;
			$request->ServerVars = base64_encode(http_build_query($_SERVER));
			$request->UserId = $UserId;
			$request->UserVar1 = $UserVar1;
			$request->UserVar2 = $UserVar2;
			
			$response = $this->Client->__soapCall('TrackOther', array($request));
			$this->Response = get_object_vars($response);
			if($this->Response['Success'])
				return true;
			else
				return false;
		}
		catch(Exception $e)
		{
			return false;
		}
	}
	
	
	/**
	 * Flag a transaction
	 * 
	 * @param unknown_type $TransactionId
	 * @param unknown_type $FlagReason
	 * @param unknown_type $FlagComment
	 * @return boolean
	 */
	public function FlagTransaction ($TransactionId, $FlagReason = '', $FlagComment = '')
	{
		$this->ResetResponse();
		try
		{
			$request = new AlphaSentryFlagRequest();
			$request->ApiKey = $this->ApiKey;
			$request->TransactionId = $TransactionId;
			$request->FlagReason = $FlagReason;
			$request->FlagComment = $FlagComment;
			
			$response = $this->Client->__soapCall('FlagTransaction', array($request));
			$this->Response = get_object_vars($response);
			if($this->Response['Success'])
				return true;
			else
				return false;
		}
		catch(Exception $e)
		{
			return false;
		}
	}
	
	
	/**
	 * Unflag a transaction
	 * 
	 * @param unknown_type $TransactionId
	 * @return boolean
	 */
	public function UnflagTransaction ($TransactionId)
	{
		$this->ResetResponse();
		try
		{
			$request = new AlphaSentryUnflagRequest();
			$request->ApiKey = $this->ApiKey;
			$request->TransactionId = $TransactionId;
			
			$response = $this->Client->__soapCall('UnflagTransaction', array($request));
			$this->Response = get_object_vars($response);
			if($this->Response['Success'])
				return true;
			else
				return false;
		}
		catch(Exception $e)
		{
			return false;
		}
	}
	

	/**
	 * Delete a transaction
	 * 
	 * @param unknown_type $TransactionId
	 * @return boolean
	 */
	public function DeleteTransaction ($TransactionId)
	{
		$this->ResetResponse();
		try
		{
			$request = new AlphaSentryFlagRequest();
			$request->ApiKey = $this->ApiKey;
			$request->TransactionId = $TransactionId;
			
			$response = $this->Client->__soapCall('DeleteTransaction', array($request));
			$this->Response = get_object_vars($response);
			if($this->Response['Success'])
				return true;
			else
				return false;
		}
		catch(Exception $e)
		{
			return false;
		}
	}
	
	
	/**
	 * Browse transactions
	 * 
	 * @param unknown_type $TransactionType
	 * @param unknown_type $StartDate
	 * @param unknown_type $EndDate
	 * @param unknown_type $MinFlagCount
	 * @param unknown_type $MaxFlagCount
	 * @param unknown_type $MinRiskScore
	 * @param unknown_type $MaxRiskScore
	 * @param unknown_type $UserVar1
	 * @param unknown_type $UserVar2
	 * @param unknown_type $ClientIp
	 * @param unknown_type $ClientDomainName
	 * @param unknown_type $MinGeoIpDistance
	 * @param unknown_type $MaxGeoIpDistance
	 * @param unknown_type $OrderBy
	 * @param unknown_type $Order
	 * @param unknown_type $Limit
	 * @param unknown_type $NextToken
	 * @return boolean
	 */
	public function BrowseTransactions ($TransactionType = '', $StartDate = '', $EndDate = '', $MinFlagCount = '', $MaxFlagCount = '', $MinRiskScore = '', $MaxRiskScore = '', $UserVar1 = '', $UserVar2 = '', $ClientIp = '', $ClientDomainName = '', $MinGeoIpDistance = '', $MaxGeoIpDistance = '', $OrderBy = '', $Order = '', $Limit = '', $NextToken = '')
	{
		$this->ResetResponse();
		try
		{
			$request = new AlphaSentryBrowseTransactionsRequest();
			$request->ApiKey = $this->ApiKey;
			$request->TransactionType = $TransactionType;
			$request->StartDate = $StartDate;
			$request->EndDate = $EndDate;
			$request->MinFlagCount = $MinFlagCount;
			$request->MaxFlagCount = $MaxFlagCount;
			$request->MinRiskScore = $MinRiskScore;
			$request->MaxRiskScore = $MaxRiskScore;
			$request->UserVar1 = $UserVar1;
			$request->UserVar2 = $UserVar2;
			$request->ClientIp = $ClientIp;
			$request->ClientDomainName = $ClientDomainName;
			$request->MinGeoIpDistance = $MinGeoIpDistance;
			$request->MaxGeoIpDistance = $MaxGeoIpDistance;
			$request->OrderBy = $OrderBy;
			$request->Order = $Order;
			$request->Limit = $Limit;
			$request->NextToken = $NextToken;
			
			$response = $this->Client->__soapCall('BrowseTransactions', array($request));
			$this->Response = get_object_vars($response);
			if($this->Response['Success'])
				return true;
			else
				return false;
		}
		catch(Exception $e)
		{
			return false;
		}
	}
	
	
	/**
	 * Get one or more specific transactions
	 * 
	 * @param unknown_type $TransactionId
	 * @param unknown_type $TransactionType
	 * @param unknown_type $DeviceId
	 * @param unknown_type $UserId
	 * @param unknown_type $UserIp
	 * @param unknown_type $UserUserAgent
	 * @param unknown_type $PurchaseId
	 * @param unknown_type $UserVar1
	 * @param unknown_type $UserVar2
	 * @param unknown_type $OrderBy
	 * @param unknown_type $Order
	 * @param unknown_type $Limit
	 * @param unknown_type $NextToken
	 * @return boolean
	 */
	public function GetTransactions ($TransactionId = '', $TransactionType = '', $DeviceId = '', $UserId = '', $UserIp = '', $UserUserAgent = '', $PurchaseId = '', $UserVar1 = '', $UserVar2 = '', $OrderBy = '', $Order = '', $Limit = '', $NextToken = '')
	{
		$this->ResetResponse();
		try
		{
			$request = new AlphaSentryTransactionsRequest();
			$request->ApiKey = $this->ApiKey;
			$request->TransactionId = $TransactionId;
			$request->TransactionType = $TransactionType;
			$request->DeviceId = $DeviceId;
			$request->UserId = $UserId;
			$request->UserIp = $UserIp;
			$request->UserUserAgent = $UserUserAgent;
			$request->PurchaseId = $PurchaseId;
			$request->UserVar1 = $UserVar1;
			$request->UserVar2 = $UserVar2;
			$request->OrderBy = $OrderBy;
			$request->Order = $Order;
			$request->Limit = $Limit;
			$request->NextToken = $NextToken;
			
			$response = $this->Client->__soapCall('GetTransactions', array($request));
			$this->Response = get_object_vars($response);
			if($this->Response['Success'])
				return true;
			else
				return false;
		}
		catch(Exception $e)
		{
			return false;
		}
	}
	
	
	/**
	 * Geocode a location string
	 * 
	 * @param unknown_type $LocationString
	 * @param unknown_type $Service
	 * @return boolean
	 */
	public function GeoCode($LocationString, $Service = 'Google')
	{
		$this->GeocodeResponse = array('Success' => false, 'Errors' => array('Query never executed'));
		try
		{
			if($Service == 'Yahoo')
			{
				// Old Yahoo API
				//$url = 'http://local.yahooapis.com/MapsService/V1/geocode?appid='.$this->GeocodeYahooApiKey.'&location='.urlencode($LocationString);
				// New Yahoo API
				$url = 'http://where.yahooapis.com/geocode?appid='.$this->GeocodeYahooApiKey.'&q='.urlencode($LocationString);
				
				$xmlString = file_get_contents($url);
				if(strstr($xmlString, 'ResultSet'))
				{
					$xml = new SimpleXMLElement($xmlString);
					$response = array();
					/*
					$response['CountryCode'] = (string)$xml->Result[0]->Country;
					$response['Latitude'] = (string)$xml->Result[0]->Latitude;
					$response['Longitude'] = (string)$xml->Result[0]->Longitude;
					$response['Address'] = (string)$xml->Result[0]->Address;
					$response['City'] = (string)$xml->Result[0]->City;
					$response['State'] = (string)$xml->Result[0]->State;
					$response['Zip'] = (string)$xml->Result[0]->Zip;
					*/
					
					$response['CountryCode'] = (string)$xml->Result[0]->countrycode;
					$response['Latitude'] = (string)$xml->Result[0]->latitude;
					$response['Longitude'] = (string)$xml->Result[0]->longitude;
					$response['City'] = (string)$xml->Result[0]->city;
					$response['State'] = (string)$xml->Result[0]->state;
					$response['Zip'] = (string)$xml->Result[0]->postal;
					$response['Success'] = true;
					$this->GeocodeResponse = $response;
					return true;
				}
				else
				{
					$this->GeocodeResponse = array('Success' => false, 'Errors' => array('No response from Yahoo Maps API Server.'));
					return false;
				}
			}
			else
			{
				$url = 'http://maps.google.com/maps/api/geocode/xml?sensor=false&address='.urlencode($LocationString);
				$xmlString = file_get_contents($url);
				$xml = new SimpleXMLElement($xmlString);
				$response = array();
				if($xml->status == 'OK')
				{
					$response['formatted_address'] = (string)$xml->result[0]->formatted_address;
					foreach($xml->result[0]->address_component as $component)
					{
						if($component->type[0] == 'country')
							$response['CountryCode'] = $component->short_name;
							
						$response[$component->type[0].'_short'] = (string)$component->short_name;
						$response[$component->type[0].'_long'] = (string)$component->long_name;	
					}
					$response['Latitude'] = $xml->result[0]->geometry->location->lat;
					$response['Longitude'] = $xml->result[0]->geometry->location->lng;
					$response['Success'] = true;
					$this->GeocodeResponse = $response;
					return true;
				}
				else
				{
					if(strlen($xml->status))
						$this->GeocodeResponse = array('Success' => false, 'Errors' => array($xml->status));
					else
						$this->GeocodeResponse = array('Success' => false, 'Errors' => array('No response from Google Maps API Server.'));
					return false;
				}
			}
		}
		catch(Exception $e)
		{
			return false;
		}
	}
	
	
	/**
	 * Remove an item from the GreyList
	 * 
	 * @param unknown_type $ItemId
	 * @param unknown_type $ListName
	 * @param unknown_type $Expires
	 * @return boolean
	 */
	public function GreyListRemoveItem($ItemId, $ListName = 'Default', $Expires = 'Hour')
	{
		try
		{
			$this->ResetGreyListResponse();
			$request = new AlphaSentryGreyListRequest();
			$request->ApiKey = $this->ApiKey;
			$request->ItemId = $ItemId;
			$request->ListName = $ListName;
			$request->Expires = $Expires;
			
			$response = $this->GreyListClient->__soapCall('RemoveItem', array($request));
			$this->GreyListResponse = get_object_vars($response);
			if($this->GreyListResponse['Success'])
				return true;
			else
				return false;
		}
		catch(Exception $e)
		{
			return false;
		}
	}
	
	
	/**
	 * Check if an item is on a GreyList
	 * 
	 * @param unknown_type $ItemId
	 * @param unknown_type $ListName
	 * @param unknown_type $Expires
	 * @return boolean
	 */
	public function GreyListCheckItem($ItemId, $ListName = 'Default', $Expires = 'Hour')
	{
		try
		{
			$this->ResetGreyListResponse();
			$request = new AlphaSentryGreyListRequest();
			$request->ApiKey = $this->ApiKey;
			$request->ItemId = $ItemId;
			$request->ListName = $ListName;
			$request->Expires = $Expires;
			
			$response = $this->GreyListClient->__soapCall('CheckItem', array($request));
			$this->GreyListResponse = get_object_vars($response);
			if($this->GreyListResponse['Success'])
				return true;
			else
				return false;
		}
		catch(Exception $e)
		{
			return false;
		}
	}
	
	
	/**
	 * Set a GreyList item value
	 * 
	 * @param unknown_type $ItemId
	 * @param unknown_type $ListName
	 * @param unknown_type $Expires
	 * @param unknown_type $Value
	 * @return boolean
	 */
	public function GreyListSetValue($ItemId, $ListName = 'Default', $Expires = 'Hour', $Value = '1')
	{
		try
		{
			$this->ResetGreyListResponse();
			$request = new AlphaSentryGreyListRequest();
			$request->ApiKey = $this->ApiKey;
			$request->ItemId = $ItemId;
			$request->ListName = $ListName;
			$request->Expires = $Expires;
			$request->Value = $Value;
			
			$response = $this->GreyListClient->__soapCall('SetValue', array($request));
			$this->GreyListResponse = get_object_vars($response);
			if($this->GreyListResponse['Success'])
				return true;
			else
				return false;
		}
		catch(Exception $e)
		{
			return false;
		}
	}
	
	
	/**
	 * Get the value of a GreyList item
	 * 
	 * @param unknown_type $ItemId
	 * @param unknown_type $ListName
	 * @param unknown_type $Expires
	 * @return boolean
	 */
	public function GreyListGetValue($ItemId, $ListName = 'Default', $Expires = 'Hour')
	{
		try
		{
			$this->ResetGreyListResponse();
			$request = new AlphaSentryGreyListRequest();
			$request->ApiKey = $this->ApiKey;
			$request->ItemId = $ItemId;
			$request->ListName = $ListName;
			$request->Expires = $Expires;
			
			$response = $this->GreyListClient->__soapCall('GetValue', array($request));
			$this->GreyListResponse = get_object_vars($response);
			if($this->GreyListResponse['Success'])
				return true;
			else
				return false;
		}
		catch(Exception $e)
		{
			return false;
		}
	}
	
	
	/**
	 * Increment the value of a GreyList item
	 * 
	 * @param unknown_type $ItemId
	 * @param unknown_type $ListName
	 * @param unknown_type $Expires
	 * @param unknown_type $Value
	 * @return boolean
	 */
	public function GreyListIncrementValue($ItemId, $ListName = 'Default', $Expires = 'Hour', $Value = '1')
	{
		try
		{
			$this->ResetGreyListResponse();
			$request = new AlphaSentryGreyListRequest();
			$request->ApiKey = $this->ApiKey;
			$request->ItemId = $ItemId;
			$request->ListName = $ListName;
			$request->Expires = $Expires;
			$request->Value = $Value;
			
			$response = $this->GreyListClient->__soapCall('IncrementValue', array($request));
			$this->GreyListResponse = get_object_vars($response);
			if($this->GreyListResponse['Success'])
				return true;
			else
				return false;
		}
		catch(Exception $e)
		{
			return false;
		}
	}
	
	
	/**
	 * Decrement the value of a GreyList item
	 * 
	 * @param unknown_type $ItemId
	 * @param unknown_type $ListName
	 * @param unknown_type $Expires
	 * @param unknown_type $Value
	 * @return boolean
	 */
	public function GreyListDecrementValue($ItemId, $ListName = 'Default', $Expires = 'Hour', $Value = '1')
	{
		try
		{
			$this->ResetGreyListResponse();
			$request = new AlphaSentryGreyListRequest();
			$request->ApiKey = $this->ApiKey;
			$request->ItemId = $ItemId;
			$request->ListName = $ListName;
			$request->Expires = $Expires;
			$request->Value = $Value;
			
			$response = $this->GreyListClient->__soapCall('DecrementValue', array($request));
			$this->GreyListResponse = get_object_vars($response);
			if($this->GreyListResponse['Success'])
				return true;
			else
				return false;
		}
		catch(Exception $e)
		{
			return false;
		}
	}
	
	
	/**
	 * Browse GreyList items
	 * 
	 * @param unknown_type $Expires
	 * @param unknown_type $Order
	 * @param unknown_type $Limit
	 * @param unknown_type $NextToken
	 * @return boolean
	 */
	public function GreyListBrowseItems($Expires, $Order = 'DESC', $Limit = 50, $NextToken = null)
	{
		try
		{
			$this->ResetGreyListResponse();
			$request = new AlphaSentryGreyListBrowseRequest();
			$request->ApiKey = $this->ApiKey;
			$request->Expires = $Expires;
			$request->Order = $Order;
			$request->Limit = $Limit;
			$request->NextToken = $NextToken;
				
			$response = $this->GreyListClient->__soapCall('BrowseItems', array($request));
			$this->GreyListResponse = get_object_vars($response);
			if($this->GreyListResponse['Success'])
				return true;
			else
				return false;
		}
		catch(Exception $e)
		{
			return false;
		}
	}
	
	
	/**
	 * Reset the Sentry API response
	 */
	private function ResetResponse()
	{
		$this->Response = array('Success' => false, 'Errors' => array('Query never executed'));
		return;
	}
	
	/**
	 * Reset the GreyList API response
	 */
	private function ResetGreyListResponse()
	{
		$this->GreyListResponse = array('Success' => false, 'Errors' => array('Query never executed'));
		return;
	}
}

class AlphaSentryTrackRequest
{
	public $ApiKey = '';
	public $ASVar1 = '';
	public $ASVar2 = '';
	public $ServerVars = '';
	public $UserId = '';
	public $PurchaseId = '';
	public $UserVar1 = '';
	public $UserVar2 = '';
	public $UserCountryCode = '';
	public $UserLongitude = '';
	public $UserLatitude = '';
	public $PurchasePostalCode = '';
	public $PurchaseStreetAddress = '';
}

class AlphaSentryFlagRequest
{
	public $ApiKey = '';
	public $TransactionId = '';
	public $FlagReason = '';
	public $FlagComment = '';
}

class AlphaSentryUnflagRequest
{
	public $ApiKey = '';
	public $TransactionId = '';
}

class AlphaSentryDeleteRequest
{
	public $ApiKey = '';
	public $TransactionId = '';
}

class AlphaSentryBrowseTransactionsRequest
{
	public $ApiKey = '';
	public $TransactionType = '';
	public $StartDate = '';
	public $EndDate = '';
	public $MinFlagCount = '';
	public $MaxFlagCount = '';
	public $MinRiskScore = '';
	public $MaxRiskScore = '';
	public $UserVar1 = '';
	public $UserVar2 = '';
	public $ClientIp = '';
	public $ClientDomainName = '';
	public $MinGeoIpDistance = '';
	public $MaxGeoIpDistance = '';
	public $OrderBy = '';
	public $Order = '';
	public $Limit = '';
	public $NextToken = '';
}

class AlphaSentryTransactionsRequest
{
	public $ApiKey = '';
	public $TransactionId = '';
	public $TransactionType = '';
	public $DeviceId = '';
	public $UserId = '';
	public $UserIp = '';
	public $UserUserAgent = '';
	public $PurchaseId = '';
	public $UserVar1 = '';
	public $UserVar2 = '';
	public $OrderBy = '';
	public $Order = '';
	public $Limit = '';
	public $NextToken = '';
}

class AlphaSentryTransaction
{
	public $TransactionId = '';
	public $UserId = '';
	public $UserIp = '';
	public $UserVar1 = '';
	public $UserVar2 = '';
	public $UserUserAgent = '';
	public $PurchaseId = '';
	public $DeviceId = '';
	public $TransactionType = '';
	public $TransactionTime = '';
	public $RiskScore = '';
	public $ClientIp = '';
	public $ClientDomainName = '';
	public $Flagged = '';
	public $DevicesPerUser = '';
	public $UsersPerDevice = '';
	public $AccountsPerIp = '';
	public $PurchasesPerIp = '';
	public $PurchasesPerDevice = '';
	public $FlagsPerDevice = '';
	public $UserCountryCode = '';
	public $GeoIpLatitude = '';
	public $GeoIpLongitude = '';
	public $GeoIpRegion = '';
	public $GeoIpCity = '';
	public $GeoIpCountryCode = '';
	public $GeoIpDistance = '';
	public $FlagReason = '';
	public $FlagComment = '';
}

class AlphaSentryGreyListRequest
{
	public $ApiKey = '';
	public $ListName = '';
	public $Value = '';
	public $ItemId = '';
	public $Expires = '';
}

class AlphaSentryGreyListBrowseRequest
{
	public $ApiKey = '';
	public $Expires = '';
	public $Order = '';
	public $Limit = '';
	public $NextToken = null;
}

class GreyListItem
{
	public $ListName = '';
	public $Value = '';
	public $ItemId = '';
	public $Expires = '';
	public $ItemTime = '';
}
?>