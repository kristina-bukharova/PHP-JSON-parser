<?php
	
class JsonParser {
	
	function decodeJson($file_path) {
		$json = file_get_contents($file_path);
		return json_decode($json, true);
	}
	
	function encodeJson($object, $file_path){
		$new_data = json_encode($object);
		file_put_contents($file_path, $new_data);
	}
	
	function isMissingEvents($file_name, $json_data) {
		$events = $json_data['events'];			
		if (empty($events)) {
			echo "ERROR: Invalid data file: " . $file_name . "\n";
			return true;
		}
	}
	
	function isMissingTicketInformation($event_obj, $index, $given_data_file) {		
		if (empty($event_obj['ticket_classes'])) {
			echo "WARNING: Data file missing 'ticket_classes' array for event number " . $index . " in file " . $given_data_file . "\n";
			return true;
		}
	}
	
	function identifyMissingFields($event_obj, $index, $given_data_file) {
		if (empty($event_obj['id'])) {
			echo "WARNING: Data file missing 'id' field for event number " . $index . " in file " . $given_data_file . "\n";
		}
		if (empty($event_obj['name']['text'])) {
			echo "WARNING: Data file missing 'name->text' field for event number " . $index . " in file " . $given_data_file . "\n";
		}
		if (empty($event_obj['url'])) {
			echo "WARNING: Data file missing 'url' field for event number " . $index . " in file " . $given_data_file . "\n";
		}
	}
	
	function transformJson($given_data_file, $new_data_file) {
		$new_json = array();
		
		$json_data = $this->decodeJson($given_data_file);
		if ($this->isMissingEvents($given_data_file, $json_data)) return;
				
		$events = $json_data['events'];
		
		$index = 1;		
		foreach ($events as $event_obj) {
			$this->identifyMissingFields($event_obj, $index, $given_data_file);
			$is_missing_ticket_info = $this->isMissingTicketInformation($event_obj, $index, $given_data_file);
			
			foreach ($event_obj as $key1 => $value1)  {
				$new_event = array();
								
				$new_event['id'] = empty($event_obj['id']) ? '' : $event_obj['id'];
				$new_event['name'] = empty($event_obj['name']['text']) ? '' : $event_obj['name']['text'];
				$new_event['url'] = empty($event_obj['url']) ? '' : $event_obj['url'];
								
				$capacity = 0;
				$sold = 0;
				if (!$is_missing_ticket_info) {
					foreach ($event_obj['ticket_classes'] as $ticket_class) {
						$capacity += $ticket_class['quantity_total'];			
						$sold += $ticket_class['quantity_sold'];
					}
					$new_event['capacity'] = $capacity;
					$new_event['sold'] = $sold;
					
					$remaining = $capacity - $sold;
					if ($remaining == 0) {
						$new_event['ticket_type'] = 'SOLD OUT';
					} elseif ($remaining < 10) {
						$new_event['ticket_type'] = 'RUSH';
					} else {
						$new_event['ticket_type'] = 'BUY';
					}
				} else {
					$new_event['capacity'] = '';
					$new_event['sold'] = '';
					$new_event['ticket_type'] = '';
				}
			}
			$index++;
			array_push($new_json, $new_event);
		}
		$this->encodeJson($new_json, $new_data_file);
		return $new_json;
	}
}


?>