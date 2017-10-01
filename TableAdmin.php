<?php

class Table_admin
{
	private $table;
	private $listRecord;
	private $config = array(
		'show' => array(
			'id' => false
		),
		'formatDate' => 'd/m/Y',
		'defaultOptionLabel' => '',
		'pageSize' => 30,
		'module' => '',
		'idField' => 'id',
		'colorScheme' => 'primary'//bootstrap 4.0 color scheme
	);
	private $editLink;
	private $deleteLink;
	private $arrayFieldShow = array();
	private $arrayFieldType = array();
	private $arrayLabel = array();
	private $arraySortable = array();
	private $arrayFieldSearch = array();
	private $arrayFieldSearchLike = array();
	private $arrayDropdownOption = array();
	private $arrayCustomSearch = array();
	private $i;
	private $total;
	private $pageSize = 30;
	private $currentPage = 1;
	const SORT_BY_FIELD = 'sort_by';
	const SORT_TYPE_FIELD = 'sort_type';
	const SEARCH_FIELD_PREFIX = 'tableAdmin_search_';
	const SEARCH_LIKE_FIELD_PREFIX = 'tableAdmin_like_';

	public function __construct($list = array(), $config = array())
	{
		if ($list) {
			$this->listRecord = $list;
			$this->i = 0;
		}

		if ($config) {
			$this->extendAttributes($config, $this->config);//TODO must add config
			$this->setDefaultLink();
		}
	}

	public static function initialize($list, $config = array())
	{
		return new Table_admin($list, $config);
	}


	private function setDefaultLink()
	{
		if ($this->config['module']) {
			$this->editLink = '/' . $this->config['module'] . '/edit/$' . $this->config['idField'];
			$this->deleteLink = '/' . $this->config['module'] . '/delete';
		}
	}

	public function setEditLink($pattern = '')
	{
		if (!$pattern) {
			$pattern = '/' . $this->config['module'] . '/edit/$' . $this->config['idField'];
		}
		$this->editLink = $pattern;
	}

	public function setDeleteLink($pattern = '')
	{
		if (!$pattern) {
			$pattern = '/' . $this->config['module'] . '/delete';
		}
		$this->deleteLink = $pattern;
	}

	public function paging($totalItems, $pageSize)
	{
		$this->total = $totalItems;
		$this->pageSize = $pageSize;
	}

	/**
	 * @param string $field
	 * @param string $label
	 * @param string $type
	 * @param bool $sortable
	 * @param bool $searchable
	 * @param bool $search_like
	 */
	public function column($field = '', $label = '', $type = '', $sortable = false, $searchable = false, $search_like = false)
	{
		$i = $this->i++;
		//add label of column
		$this->arrayLabel[$i] = $label;
		$this->arraySortable[$i] = $sortable;
		$this->arrayFieldSearch[$i] = (bool)$searchable;
		$this->arrayFieldSearchLike[$i] = (bool)$search_like;
		$this->arrayFieldShow[$i] = $field;
		$this->arrayFieldType[$i] = $type;
	}

	public function columnDropdown($field = '', $label = '', $option = array(), $sortable = false, $searchable = false, $search_like = false)
	{
		$i = $this->i++;
		//add label of column
		$this->arrayLabel[$i] = $label;
		$this->arraySortable[$i] = $sortable;
		$this->arrayFieldSearch[$i] = !!($searchable);
		$this->arrayFieldSearchLike[$i] = !!($search_like);
		$this->arrayFieldShow[$i] = $field;
		$this->arrayFieldType[$i] = 'dropdown';
		$this->arrayDropdownOption[$i] = $option;
	}

	public function columnSearchDropdown($field = '', $label = '', $option = array(), $sortable = false, $searchable = false, $search_like = false)
	{
		$i = $this->i++;
		//add label of column
		$this->arrayLabel[$i] = $label;
		$this->arraySortable[$i] = $sortable;
		$this->arrayFieldSearch[$i] = !!($searchable);
		$this->arrayFieldSearchLike[$i] = !!($search_like);
		$this->arrayFieldShow[$i] = $field;
		$this->arrayFieldType[$i] = 'search_dropdown';
		$this->arrayDropdownOption[$i] = $option;
	}

	public function render()
	{
		$this->renderTable();
		return $this->table;
	}

	public static function getSort()
	{
		if (get_instance()->input->get(self::SORT_BY_FIELD, true)) {
			return array(snake_case(get_instance()->input->get(self::SORT_BY_FIELD, true)) => get_instance()->input->get(self::SORT_TYPE_FIELD, true));
		} else
			return array();
	}

	public static function getSearch()
	{
		$arr = array();
		foreach ($_GET as $field => $item) {
			if ($item == '0') {
				$field = snake_case(str_replace(self::SEARCH_FIELD_PREFIX, '', $field));
				$arr[$field] = $item;
			}
			if ($item && strpos($field, self::SEARCH_FIELD_PREFIX) !== FALSE) {
				$field = snake_case(str_replace(self::SEARCH_FIELD_PREFIX, '', $field));
				$arr[$field] = $item;
			}
			if ($item && strpos($field, self::SEARCH_LIKE_FIELD_PREFIX) !== FALSE) {
				$field = snake_case(str_replace(self::SEARCH_LIKE_FIELD_PREFIX, '', $field));
				$arr[$field] = array($field, 'like', $item);
			}
		}

		foreach ($arr as $field => &$value) {
			if (is_string($value)) {
				$value = array($field, '=', $value);
			}
		}
		return $arr;
	}

	/**
	 * @param $field
	 * @param null $defaultValue | option value
	 * @param $type : type 1: input text, type 2: option
	 * @param string $label placeholder
	 * @param bool $searchLike
	 * @return $this
	 */
	public function addSearch($field, $type, $label = '',$defaultValue = null, $searchLike = false)
	{
		$this->arrayCustomSearch[$field] = array('value' => $defaultValue, 'type' => $type, 'like' => $searchLike, 'label'=>$label);
		return $this;
	}

	private function generateCustomSearch()
	{
		if (!$this->arrayCustomSearch) {
			return '';
		}
		$str = '';
		foreach ($this->arrayCustomSearch as $field => $search) {
			$controlName = $search['like'] ? Table_Admin::SEARCH_LIKE_FIELD_PREFIX . $field : Table_Admin::SEARCH_FIELD_PREFIX . $field;
			$value = get_instance()->input->get($controlName, true) ?: '';
			switch ($search['type']) {
				case 1:
					$str .= '<div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
<input type="text" placeholder="' . $search['label'] . '" class="form-control tableAdmin-csearch" name="' . $controlName . '" value="' . $value . '">
</div>';
					break;
				case 2:
					if (is_array($search['value'])) {
						$options = '<option value=""> -- '.$search['label'].' -- </option>';
						foreach ($search['value'] as $k => $v) {
							$options .= '<option value="' . $k . '" ' . ($value == $k ? 'selected' : '') . '>' . $v . '</option>';
						}
						$str .= '<div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
									<select class="form-control tableAdmin-csearch" 
										name="' . $controlName . '">' . $options . '</select>	
								</div>';
					}
					break;
			}
		}
		return $str;
	}

	private function showHeader()
	{
		$arrayFieldSearch = $this->arrayFieldSearch;
		$arrayFieldSearch = array_filter($arrayFieldSearch);
		if (empty($arrayFieldSearch) && empty($this->arrayCustomSearch)) {
			return '';
		}
		$str = '<form>';
		$str .= '<div class="row">';
		foreach ($this->arrayFieldShow as $i => $field) {
			if ($this->arrayFieldSearch[$i]) {
				$input_name = $this->arrayFieldSearchLike[$i] ? self::SEARCH_LIKE_FIELD_PREFIX . $field : self::SEARCH_FIELD_PREFIX . $field;
				$value = get_instance()->input->get($input_name, true);
				$value = $value ?: '';
				$str .= '<div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">';
				if ($this->arrayFieldType[$i] == 'dropdown' || $this->arrayFieldType[$i] == 'search_dropdown') {
					$str .= '<select class="form-control select2" name="' . $input_name . '"
								id="' . $input_name . '">';
					$str .= '<option value=""> -- ' . $this->arrayLabel[$i] . ' -- </option>';
					foreach ($this->arrayDropdownOption[$i] as $key => $option) {
						$str .= '<option value="' . $key . '" ' . ($value !== '' && $value == $key ? 'selected' : '') . '>' . $option . '</option>';
					}
					$str .= '</select>';
				} else {
					$str .= '<input type="text" value="' . $value . '" 
								id="' . $input_name . '"
						       class="form-control" name="' . $input_name . '" 
						       placeholder="' . $this->arrayLabel[$i] . '">';
				}
				$str .= '</div>';
			}
		}
		$str .= $this->generateCustomSearch();
		$str .= '<div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
					<button class="btn btn-flat btn-' . $this->config['colorScheme'] . '">Search</button>
				</div>
				</div>
			</form>';
		return $str;
	}

	private function showFooter()
	{
		if (!$this->total) {
			$this->total = count($this->listRecord);
		}
		$pagination = $this->getPaginationString();
		$from = !$this->listRecord ? 0 : ($this->currentPage - 1) * $this->pageSize + 1;
		$to = $this->currentPage * $this->pageSize > $this->total ? $this->total : $this->currentPage * $this->pageSize;
		$footer = '<div class="row">';
		$footer .= '<div class="col-sm-5">
                            <div class="tableAdmin-info" role="status" aria-live="polite">
                            Showing ' . $from
			. ' to ' . $to . ' of ' . $this->total . ' entries
                            </div>
                        </div>';
		$footer .= '    <div class="col-sm-7">';
		$footer .= '<div class="tableAdmin-paginate">';
		$footer .= $pagination;
		$footer .= '</div>';
		$footer .= '</div>';
		$footer .= '</div>';
		return $footer;
	}

	private function uriString()
	{
		return strtok($_SERVER['REQUEST_URI'], '?');
	}

	private function queryString()
	{
		return $_SERVER['QUERY_STRING'];
	}

	private function parseQueryParams()
	{
		$query = $this->queryString();
		parse_str($query, $params);
		return $params;
	}

	private function addQueryParams($arrayParams)
	{
		$targetPage = $this->uriString();
		$queryParams = $this->parseQueryParams();
		foreach ($arrayParams as $param => $value) {
			$queryParams[$param] = $value;
		}
		return $targetPage . '?' . http_build_query($queryParams);
	}

	//function to return the pagination string
	private function getPaginationString($adjacents = 1, $pageParam = 'page')
	{
		//defaults
		if (!$adjacents) $adjacents = 1;
		$limit = $this->pageSize;
		$totalItems = $this->total;
		if (!$pageParam) $pageParam = 'page';
		$targetPage = $this->uriString();
		$queryParams = $this->parseQueryParams();

		if (isset($queryParams[$pageParam])) {
			$this->currentPage = $queryParams[$pageParam];
			unset($queryParams[$pageParam]);
		} else {
			$this->currentPage = 1;
		}
		$targetPage .= '?' . http_build_query($queryParams);
		$pageString = $queryParams ? '&page=' : 'page=';
		//other vars
		$prev = $this->currentPage - 1;                                    //previous page is page - 1
		$next = $this->currentPage + 1;                                    //next page is page + 1
		$lastPage = ceil($totalItems / $limit);                //lastpage is = total items / items per page, rounded up.
		$lpm1 = $lastPage - 1;                                //last page minus 1

		/*
            Now we apply our rules and draw the pagination object.
            We're actually saving the code to a variable in case we want to draw it more than once.
        */
		$pagination = '';
		if ($lastPage > 1) {
			$pagination .= '<ul class="pagination">';

			//previous button
			if ($this->currentPage > 1)
				$pagination .= '<li class="page-item previous"><a class="page-link" href="' . $targetPage . $pageString . $prev . '">Previous</a></li>';
			else
				$pagination .= '<li class="page-item previous disabled"><a class="page-link" href="#">Prev</a></li>';

			//pages
			if ($lastPage < 7 + ($adjacents * 2))    //not enough pages to bother breaking it up
			{
				for ($counter = 1; $counter <= $lastPage; $counter++) {
					if ($counter == $this->currentPage)
						$pagination .= '<li class="page-item active"><a class="page-link" href="#">' . $counter . '</a></li>';
					else
						$pagination .= '<li class="page-item"><a class="page-link" href="' . $targetPage . $pageString . $counter . '">' . $counter . '</a></li>';
				}
			} elseif ($lastPage >= 7 + ($adjacents * 2))    //enough pages to hide some
			{
				//close to beginning; only hide later pages
				if ($this->currentPage < 1 + ($adjacents * 3)) {
					for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++) {
						if ($counter == $this->currentPage)
							$pagination .= '<li class="page-item active"><a class="page-link" href="#">' . $counter . '</a></li>';
						else
							$pagination .= '<li class="page-item"><a class="page-link" href="' . $targetPage . $pageString . $counter . '">' . $counter . '</a></li>';
					}
					$pagination .= '<li class="page-item"><span class="elipses page-link">...</span></li>';
					$pagination .= '<li class="page-item"><a class="page-link" href="' . $targetPage . $pageString . $lpm1 . '">' . $lpm1 . '</a></li>';
					$pagination .= '<li class="page-item"><a class="page-link" href="' . $targetPage . $pageString . $lastPage . '">' . $lastPage . '</a></li>';
				} //in middle; hide some front and some back
				elseif ($lastPage - ($adjacents * 2) > $this->currentPage && $this->currentPage > ($adjacents * 2)) {
					$pagination .= '<li class="page-item"><a class="page-link" href="' . $targetPage . $pageString . '1">1</a></li>';
					$pagination .= '<li class="page-item"><a class="page-link" href="' . $targetPage . $pageString . '2">2</a></li>';
					$pagination .= '<li class="page-item"><span class="elipses page-link">...</span></li>';
					for ($counter = $this->currentPage - $adjacents; $counter <= $this->currentPage + $adjacents; $counter++) {
						if ($counter == $this->currentPage)
							$pagination .= '<li class="page-item active"><a class="page-link" href="#">' . $counter . '</a></li>';
						else
							$pagination .= '<li class="page-item"><a class="page-link" href="' . $targetPage . $pageString . $counter . '">' . $counter . '</a></li>';
					}
					$pagination .= '<li class="page-item"><span class="elipses page-link">...</span></li>';
					$pagination .= '<li class="page-item"><a class="page-link" href="' . $targetPage . $pageString . $lpm1 . '">' . $lpm1 . '</a></li>';
					$pagination .= '<li class="page-item"><a class="page-link" href="' . $targetPage . $pageString . $lastPage . '">' . $lastPage . '</a></li>';
				} //close to end; only hide early pages
				else {
					$pagination .= '<li class="page-item"><a class="page-link" href="' . $targetPage . $pageString . '1">1</a></li>';
					$pagination .= '<li class="page-item"><a class="page-link" href="' . $targetPage . $pageString . '2">2</a></li>';
					$pagination .= '<li class="page-item"><span class="elipses page-link">...</span></li>';
					for ($counter = $lastPage - (1 + ($adjacents * 3)); $counter <= $lastPage; $counter++) {
						if ($counter == $this->currentPage)
							$pagination .= '<li class="page-item active"><a class="page-link" href="#">' . $counter . '</a></li>';
						else
							$pagination .= '<li class="page-item"><a class="page-link" href="' . $targetPage . $pageString . $counter . '">' . $counter . '</a></li>';
					}
				}
			}

			//next button
			if ($this->currentPage < $counter - 1)
				$pagination .= '<li class="page-item"><a class="page-link" href="' . $targetPage . $pageString . $next . '">Next</a></li>';
			else
				$pagination .= '<li class="page-item disabled"><a class="page-link" href="#">Next</a></li>';
			$pagination .= "</ul>";
		}

		return $pagination;

	}

	private function parseLink($link, $dataObject)
	{
		$exp = explode('/', $link);
		$params = array();
		if ($exp) {
			foreach ($exp as $section) {
				if (starts_with($section, '$')) {
					$params[] = $section;
				}
			}
		}
		foreach ($params as $param) {
			$param_name = substr($param, 1);
			$link = str_replace($param, $this->prepareFieldName($dataObject, $param_name), $link);
		}
		return $link;
	}


	private function th()
	{
		$str = '<tr>';
		if ($this->config['show']['id']) {
			$str .= '<th class="text-center" style="width:40px"><input type="checkbox" class="checkbox-all iCheck" id="checkbox-all"></th>';
		}
		foreach ($this->arrayLabel as $key => $label) {
			$str .= '<th class="text-center bg-' . $this->config['colorScheme'] . '" style="vertical-align: middle">' . $label . ' ' . $this->addSort($key) . '</th>';
		}
		return $str . '</tr>';
	}

	private function addSort($column_key)
	{
		if ($this->arraySortable[$column_key]) {
			$sort_by = get_instance()->input->get(self::SORT_BY_FIELD, true);
			$current_sort_type = get_instance()->input->get(self::SORT_TYPE_FIELD, true);
			$current_sort_type = strtolower($current_sort_type);
			$current_sort_type = $current_sort_type == 'asc' ? 'desc' : 'asc';
			$sortField = is_string($this->arraySortable[$column_key])
				? $this->arraySortable[$column_key]
				: $this->arrayFieldShow[$column_key];
			$url = $this->addQueryParams(array(
				self::SORT_BY_FIELD => $sortField,
				self::SORT_TYPE_FIELD => $current_sort_type
			));
			$class_icon = $current_sort_type == 'asc' && ($this->arrayFieldShow[$column_key] == $sort_by || $this->arraySortable[$column_key] == $sort_by)
				? 'fa-sort-amount-desc'
				: 'fa-sort-amount-asc';
			return '<a href="' . $url . '" style="color: #fff"><i class="fa ' . $class_icon . '"></i></a>';
		}
		return '';
	}

	private function start_tr($record_id)
	{
		if (!$this->config['show']['id'])
			return '';
		$str = '<td class="text-center">' . $this->generateCheckbox(array(
				'name' => 'row-checkbox-' . $record_id,
				'id' => 'row-checkbox-' . $record_id,
				'data-id' => $record_id,
				'class' => 'form-control iCheck row-checkbox',
				'value' => 0
			)) . '</td>';
		return $str;
	}

	private function tr($row_data, $record_id)
	{
		$str = '<tr id="tableAdmin-tr-' . $record_id . '" data-id="' . $record_id . '">';
		$str .= $this->start_tr($record_id);
		foreach ($this->arrayFieldShow as $key => $fieldName) {
			$type = $this->arrayFieldType[$key];
			$type = explode(':', $type);
			$align = isset($type[1]) ? $type[1] : '';
			$type = $type[0];
			$value = !in_array($type, array('value', 'edit', 'delete')) ? $this->prepareFieldName($row_data, $fieldName) : $fieldName;

			switch ($type) {
				case 'checkbox':
					$align_default = $align ? $align : 'center';
					$str .= '<td class="text-' . $align_default . '">' . $this->generateCheckbox(array(
							'name' => 'control-' . $fieldName . '-' . $record_id,
							'id' => 'control-' . $fieldName . '-' . $record_id,
							'data-id' => $record_id,
							'class' => 'form-control iCheck control-' . $fieldName,
							'value' => $value
						)) . '</td>';
					break;
				case 'image':
					$align_default = $align ? $align : 'center';
					$str .= '<td class="text-center">' .
						$this->generateImage(array(
							'name' => 'control-' . $fieldName . '-' . $record_id,
							'id' => 'control-' . $fieldName . '-' . $record_id,
							'data-id' => $record_id,
							'class' => 'control-' . $fieldName,
							'imagePath' => $value
						))
						. '</td>';
					break;
				case 'dateint':
					$align_default = $align ? $align : 'center';
					$str .= '<td class="text-' . $align_default . '">' . date($this->config['formatDate'], $value) . '</td>';
					break;
				case 'date':
				case 'datetime':
					$align_default = $align ? $align : 'center';
					$str .= '<td class="text-' . $align_default . '">' . $value . '</td>';
					break;
				case 'url':
					$align_default = $align ? $align : 'center';
					$str .= '<td class="text-' . $align_default . '"><a href="' . $value . '" target="_blank">Xem</a></td>';
					break;
				case 'dropdown':
					$str .= '<td>' . $this->generateDropdown(array(
							'name' => 'control-' . $fieldName . '-' . $record_id,
							'id' => 'control-' . $fieldName . '-' . $record_id,
							'data-id' => $record_id,
							'class' => 'form-control select2 dropdown control-' . $fieldName,
							'value' => $value,
							'option' => $this->arrayDropdownOption[$key]
						)) . '</td>';
					break;
				case 'edit':
					$align_default = $align ? $align : 'center';
					$str .= '<td class="text-' . $align_default . '">
								<a data-id="' . $record_id . '" href="' . $this->parseLink($this->editLink, $row_data) . '">
									<i class="fa fa-edit text-' . $this->config['colorScheme'] . '"></i>
								</a>
							</td>';
					break;
				case 'delete':
					$align_default = $align ? $align : 'center';
					$str .= '<td class="text-' . $align_default . '">
								<a href="#" class="deleteRecord"
								    data-id="' . $record_id . '"
								    data-delete-url="' . $this->parseLink($this->deleteLink, $row_data) . '">
								<i class="fa fa-trash ' . $this->config['colorScheme'] . '"></i></a>
							</td>';
					break;
				case 'text':
					$align_default = $align ? $align : 'left';
					$str .= '<td class="text-' . $align_default . '">' . $value . '</td>';
					break;
				case 'number':
					$align_default = $align ? $align : 'right';
					$str .= '<td class="text-' . $align_default . '">' . number_format(intval($value)) . '</td>';
					break;
				case 'activebox':
					$align_default = $align ? $align : 'center';
					$str .= '<td class="text-' . $align_default . '">' . $this->generateActive(array(
							'name' => 'control-' . $fieldName . '-' . $record_id,
							'id' => 'control-' . $fieldName . '-' . $record_id,
							'data-id' => $record_id,
							'class' => 'form-control iCheck control-' . $fieldName,
							'value' => $value
						)) . '</td>';
					break;
				default:
					$align_default = $align ? $align : 'left';
					$str .= '<td class="text-' . $align_default . '">' . $value . '</td>';
					break;
			}
		}
		return $str . '</tr>';
	}

	private function prepareFieldName($dataObject, $string = '')
	{
		//check if $string include callable function
		$fn = '';
		if ($string) {
			$arr = explode('|', $string);
			if (count($arr) == 2) {
				$fn = $arr[1];
				$string = $arr[0];
			}
		}
		$arr = explode('.', $string);
		if ($arr) {
			foreach ($arr as $property) {
				$propertyArr = explode('*', $property);
				if (is_object($dataObject)) {
					$dataObject = property_exists($dataObject, $propertyArr[0]) ? $dataObject->{$propertyArr[0]} : '';

				} elseif (is_array($dataObject)) {
					$dataObject = isset($dataObject[$propertyArr[0]]) ? $dataObject[$propertyArr[0]] : '';
				}

				for ($i = 1; $i <= count($propertyArr) - 1; $i++) {
					if (isset($propertyArr[$i])) {
						$dataObject = isset($dataObject[$propertyArr[$i]]) ? $dataObject[$propertyArr[$i]] : '';
					}
				}

				continue;
			}
		}

		if ($fn && is_callable($fn)) {
			$dataObject = $fn($dataObject);
		}
		return $dataObject;
	}

	private function renderTable()
	{
		$this->table = $this->showHeader();
		$this->table .= '<div class="row"><div class="col-lg-12"><table class="table table-bordered table-striped table-hover tableAdmin">';
		$this->table .= $this->th();
		if ($this->listRecord) {
			foreach ($this->listRecord as $i => $record) {
				if (property_exists($record, 'id')) {
					$id = $record->id;
				} else {
					$id = $i;
				}
				$this->table .= $this->tr($record, $id);
			}
		}
		$this->table .= '</table>';
		$this->table .= '</div></div>';
		$this->table .= $this->showFooter();
	}

	private function generateActive($attribute = array())
	{
		$default = $this->controlDefaultValue();
		$this->extendAttributes($attribute, $default);
		$default['checked'] = $default['value'] ? 'checked' : '';
		$default['data-id'] = isset($attribute['data-id']) ? ' data-id="' . $attribute['data-id'] . '" ' : '';
		return '<div class="list-control-item text-center">
            <div class="icheckbox_minimal-blue ' . $default['checked'] . ' item-block" onclick="toggleBlock(' . $attribute['data-id'] . ')" style="position: relative;"></div>
        </div>';
	}

	private function generateCheckbox($attribute = array())
	{
		$default = $this->controlDefaultValue();
		$this->extendAttributes($attribute, $default);
		$default['checked'] = $default['value'] ? 'checked' : '';
		$default['data-id'] = isset($attribute['data-id']) ? ' data-id="' . $attribute['data-id'] . '" ' : '';
		return '<div class="list-control-item text-center">
					<input type="checkbox"
							' . $this->defaultControlAttribute($default) . '
							' . $default['data-id'] . '
							value="1"
							' . $default['checked'] . '/>
				</div>';
	}

	private function generateImage($attribute = array())
	{
		$default = $this->controlDefaultValue();
		$this->extendAttributes($attribute, $default);
		return '<div class="tableAdmin-imageThumb"><img src="' . $default['imagePath'] . '"></div>';
	}

	private function defaultOption($label = '', $value = '')
	{
		$label = $label ?: $this->config['defaultOptionLabel'];
		return '<option value="' . $value . '">' . $label . '</option>';
	}

	private function generateDropdown($attribute = array())
	{
		$default = $this->controlDefaultValue();
		$this->extendAttributes($attribute, $default);
		$default['defaultValue'] = isset($default['defaultValue']) ? $default['defaultValue'] : '';
		$opts = $this->defaultOption($default['label'], $default['defaultValue']);
		$default['data-id'] = isset($attribute['data-id']) ? ' data-id="' . $attribute['data-id'] . '" ' : '';
		foreach ($default['option'] as $key => $val) {
			$selected = $default['value'] == $key ? 'selected' : '';
			$opts .= '<option value="' . $key . '" ' . $selected . '>' . $val . '</option>';
		}
//	    echo $default['value'];
		return '<div class="list-control-item" style="max-width: 200px;margin: 0 auto">
					<select ' . $this->defaultControlAttribute($default) . $default['data-id'] . '>
						' . $opts . '
					</select>
				</div>';
	}

	private function defaultControlAttribute($default)
	{
		$default['id'] = str_replace(array('#', '.', '*', '|'), '-', $default['id']);
		$default['class'] = str_replace(array('#', '.', '*', '|'), '-', $default['class']);
		return ' name="' . $default['name'] . '" id="' . $default['id'] . '" class="' . $default['class'] . '" ';
	}

	private function extendAttributes($attributes, &$default)
	{
		if (is_array($attributes)) {
			foreach ($default as $key => $val) {
				if (isset($attributes[$key])) {
					$default[$key] = $attributes[$key];
					unset($attributes[$key]);
				}
			}
			if (count($attributes) > 0) {
				$default = array_merge($default, $attributes);
			}
		}
		return $default;
	}

	private function controlDefaultValue()
	{
		return array(
			'name' => '',
			'label' => '',
			'id' => '',
			'value' => '',
			'title' => '',
			'class' => ''
		);
	}
}
