<?php
namespace Solid\Builder;
class TableAdmin
{
    private $table;
    private $listRecord;
    private $config = [
        'show' => [
            'id' => true
        ],
        'formatDate' => 'd/m/Y',
        'defaultOptionLabel' => '',
        'pageSize' => 30,
        'module'=>'',
        'idField'=>'id',
    ];
    private $editLink;
    private $deleteLink;
    private $arrayFieldShow = [];
    private $arrayFieldType = [];
    private $arrayLabel = [];
    private $arraySortable = [];
    private $arrayFieldSearch = [];
    private $arrayDropdownOption = [];
    private $i;
    private $total;
    private $pageSize = 30;
    private $currentPage = 1;

    public function __construct($list = [], $config = [])
    {
        if($list) {
            $this->listRecord = $list;
            $this->i = 0;
        }
        
        if ($config) {
            $this->extendAttributes($config, $this->config);//TODO must add config
            $this->setDefaultLink();
        }
    }
    
    public static function initialize($list, $config = []) {
        return new TableAdmin($list,$config);
    }
    

    private function setDefaultLink() {
        if($this->config['module']) {
            $this->editLink = '/admin/' . $this->config['module'] . '/edit/$' . $this->config['idField'];
            $this->deleteLink = '/admin/' . $this->config['module'] . '/delete';
        }
    }
    
    public function setEditLink($pattern = '')
    {
        if(!$pattern) {
            $pattern = '/admin/' . $this->config['module'] . '/edit/$' . $this->config['idField']; 
        }
        $this->editLink = $pattern;
    }
    public function setDeleteLink($pattern = '') {
        if(!$pattern) {
            $pattern = '/admin/' . $this->config['module'] . '/delete';
        }
        $this->deleteLink = $pattern;
    }

    public function paging($totalItems, $pageSize)
    {
        $this->total = $totalItems;
        $this->pageSize = $pageSize;
    }

    public function column($field = '', $label = '', $type = '', $sortable = false, $searchable = false)
    {
        $i = $this->i++;
        //add label of column
        $this->arrayLabel[$i] = $label;
        $this->arraySortable[$i] = !!($sortable);
        $this->arrayFieldSearch[$i] = !!($searchable);
        $this->arrayFieldShow[$i] = $field;
        $this->arrayFieldType[$i] = $type;
    }

    public function columnDropdown($field = '', $label = '', $option = [], $sortable = false, $searchable = false)
    {
        $i = $this->i++;
        //add label of column
        $this->arrayLabel[$i] = $label;
        $this->arraySortable[$i] = !!($sortable);
        $this->arrayFieldSearch[$i] = !!($searchable);
        $this->arrayFieldShow[$i] = $field;
        $this->arrayFieldType[$i] = 'dropdown';
        $this->arrayDropdownOption[$i] = $option;
    }

    public function render()
    {
        $this->renderTable();
        return $this->table;
    }

    private function showHeader()
    {
        return '';
    }

    private function showFooter()
    {
        if(!$this->total) {
            $this->total = count($this->listRecord);
        }
        $pagination = $this->getPaginationString();
        $from = !$this->listRecord ? 0 : ($this->currentPage - 1) * $this->pageSize + 1;
        $to = $this->currentPage * $this->pageSize > $this->total ? $this->total : $this->currentPage * $this->pageSize;
        $footer =   '<div class="row">';
        $footer .=      '<div class="col-sm-5">
                            <div class="tableAdmin-info" role="status" aria-live="polite">
                            Showing '.$from
                            .' to '.$to.' of '.$this->total.' entries
                            </div>
                        </div>';
        $footer .= '    <div class="col-sm-7">';
        $footer .=          '<div class="tableAdmin-paginate">';
        $footer .=              $pagination;
        $footer .=          '</div>';
        $footer .=      '</div>';
        $footer .=  '</div>';
        return $footer;
    }

    private function uriString() {
        return strtok($_SERVER['REQUEST_URI'],'?');
    }
    private function queryString() {
        return $_SERVER['QUERY_STRING'];
    }
    private function parseQueryParams() {
        $query = $this->queryString();
        parse_str($query, $params);
        return $params;
    }

    //function to return the pagination string
    private function getPaginationString($adjacents = 1, $pageParam = 'page')
    {
        //defaults
        if(!$adjacents) $adjacents = 1;
        $limit = $this->pageSize;
        $totalItems = $this->total;
        if(!$pageParam) $pageParam = 'page';
        $targetPage = $this->uriString();
        $queryParams = $this->parseQueryParams();

        if(isset($queryParams[$pageParam])) {
            $this->currentPage = $queryParams[$pageParam];
            unset($queryParams[$pageParam]);
        }else{
            $this->currentPage = 1;
        }
        $targetPage .= '?' . http_build_query($queryParams);
        $pageString = $queryParams ? '&page=' : 'page=';
        //other vars
        $prev = $this->currentPage - 1;									//previous page is page - 1
        $next = $this->currentPage + 1;									//next page is page + 1
        $lastPage = ceil($totalItems / $limit);				//lastpage is = total items / items per page, rounded up.
        $lpm1 = $lastPage - 1;								//last page minus 1

        /*
            Now we apply our rules and draw the pagination object.
            We're actually saving the code to a variable in case we want to draw it more than once.
        */
        $pagination = '';
        if($lastPage > 1)
        {
            $pagination .= '<ul class="pagination">';

            //previous button
            if ($this->currentPage > 1)
                $pagination .= '<li class="paginate_button previous"><a href="' . $targetPage . $pageString . $prev . '">Previous</a></li>';
            else
                $pagination .= '<li class="paginate_button previous disabled"><a href="#">Prev</a></li>';

            //pages
            if ($lastPage < 7 + ($adjacents * 2))	//not enough pages to bother breaking it up
            {
                for ($counter = 1; $counter <= $lastPage; $counter++)
                {
                    if ($counter == $this->currentPage)
                        $pagination .= '<li class="paginate_button active"><a href="#">'.$counter.'</a></li>';
                    else
                        $pagination .= '<li class="paginate_button"><a href="' . $targetPage . $pageString . $counter . '">'.$counter.'</a></li>';
                }
            }
            elseif($lastPage >= 7 + ($adjacents * 2))	//enough pages to hide some
            {
                //close to beginning; only hide later pages
                if($this->currentPage < 1 + ($adjacents * 3))
                {
                    for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
                    {
                        if ($counter == $this->currentPage)
                            $pagination .= '<li class="paginate_button active"><a href="#">'.$counter.'</a></li>';
                        else
                            $pagination .= '<li class="paginate_button"><a href="' . $targetPage . $pageString . $counter . '">'.$counter.'</a></li>';
                    }
                    $pagination .= '<li class="paginate_button"><span class="elipses">...</span></li>';
                    $pagination .= '<li class="paginate_button"><a href="' . $targetPage . $pageString . $lpm1 . '">'.$lpm1.'</a></li>';
                    $pagination .= '<li class="paginate_button"><a href="' . $targetPage . $pageString . $lastPage . '">'.$lastPage.'</a></li>';
                }
                //in middle; hide some front and some back
                elseif($lastPage - ($adjacents * 2) > $this->currentPage && $this->currentPage > ($adjacents * 2))
                {
                    $pagination .= '<li class="paginate_button"><a href="' . $targetPage . $pageString . '1">1</a></li>';
                    $pagination .= '<li class="paginate_button"><a href="' . $targetPage . $pageString . '2">2</a></li>';
                    $pagination .= '<li class="paginate_button"><span class="elipses">...</span></li>';
                    for ($counter = $this->currentPage - $adjacents; $counter <= $this->currentPage + $adjacents; $counter++)
                    {
                        if ($counter == $this->currentPage)
                            $pagination .= '<li class="paginate_button active"><a href="#">'.$counter.'</a></li>';
                        else
                            $pagination .= '<li class="paginate_button"><a href="' . $targetPage . $pageString . $counter . '">'.$counter.'</a></li>';
                    }
                    $pagination .= '<li class="paginate_button"><span class="elipses">...</span></li>';
                    $pagination .= '<li class="paginate_button"><a href="' . $targetPage . $pageString . $lpm1 . '">'.$lpm1.'</a></li>';
                    $pagination .= '<li class="paginate_button"><a href="' . $targetPage . $pageString . $lastPage . '">'.$lastPage.'</a></li>';
                }
                //close to end; only hide early pages
                else
                {
                    $pagination .= '<li class="paginate_button"><a href="' . $targetPage . $pageString . '1">1</a></li>';
                    $pagination .= '<li class="paginate_button"><a href="' . $targetPage . $pageString . '2">2</a></li>';
                    $pagination .= '<li class="paginate_button"><span class="elipses">...</span></li>';
                    for ($counter = $lastPage - (1 + ($adjacents * 3)); $counter <= $lastPage; $counter++)
                    {
                        if ($counter == $this->currentPage)
                            $pagination .= '<li class="paginate_button active"><a href="#">'.$counter.'</a></li>';
                        else
                            $pagination .= '<li class="paginate_button"><a href="' . $targetPage . $pageString . $counter . '">'.$counter.'</a></li>';
                    }
                }
            }

            //next button
            if ($this->currentPage < $counter - 1)
                $pagination .= '<li class="paginate_button"><a href="' . $targetPage . $pageString . $next . '">Next</a></li>';
            else
                $pagination .= '<li class="paginate_button disabled"><a href="#">Next</a></li>';
            $pagination .= "</ul>";
        }

        return $pagination;

    }

    private function parseLink($link, $dataObject)
    {
        $exp = explode('/', $link);
        $params = [];
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
        foreach ($this->arrayLabel as $key => $label) {
            $str .= '<th class="text-center bg-primary" style="vertical-align: middle">' . $label . '</th>';
        }
        return $str . '</tr>';
    }

    private function tr($row_data, $record_id)
    {
        $str = '<tr id="tableAdmin-tr-' . $record_id . '">';
        foreach ($this->arrayFieldShow as $key => $fieldName) {
            $type = $this->arrayFieldType[$key];
            $value = !in_array($type, ['value', 'edit', 'delete']) ? $this->prepareFieldName($row_data, $fieldName) : $fieldName;

            switch ($type) {
                case 'checkbox':
                    $str .= '<td class="text-center">' . $this->generateCheckbox([
                            'name' => 'control-' . $fieldName . '-' . $record_id,
                            'id' => 'control-' . $fieldName . '-' . $record_id,
                            'data-id'=>$record_id,
                            'class' => 'form-control iCheck control-' . $fieldName,
                            'value' => $value
                        ]) . '</td>';
                    break;
                case 'image':
                    $str .= '<td class="text-center">'.
                        $this->generateImage([
                            'name' => 'control-' . $fieldName . '-' . $record_id,
                            'id' => 'control-' . $fieldName . '-' . $record_id,
                            'data-id'=>$record_id,
                            'class'=>'control-' . $fieldName,
                            'imagePath'=>$value
                        ])
                        .'</td>';
                    break;
                case 'datetime':
                    $str .= '<td class="text-center">' . date($this->config['formatDate'], $value) . '</td>';
                    break;
                case 'dropdown':
                    $str .= '<td>' .$this->generateDropdown([
                            'name' => 'control-' . $fieldName . '-' . $record_id,
                            'id' => 'control-' . $fieldName . '-' . $record_id,
                            'data-id'=>$record_id,
                            'class' => 'form-control dropdown control-'.$fieldName,
                            'value' => $value,
                            'option' => $this->arrayDropdownOption[$key]
                        ]) . '</td>';
                    break;
                case 'edit':
                    $str .= '<td class="text-center">
								<a data-id="'.$record_id.'" href="' . $this->parseLink($this->editLink, $row_data) . '">
									<i class="fa fa-edit"></i>
								</a>
							</td>';
                    break;
                case 'delete':
                    $str .= '<td class="text-center">
								<a href="#" class="deleteRecord" 
								    data-id="' . $record_id . '"
								    data-delete-url="'.$this->parseLink($this->deleteLink, $row_data).'">
								<i class="fa fa-trash"></i></a>
							</td>';
                    break;
                case 'value':
                default:
                    $td_class = '';
                    if (is_numeric($value)) {
                        $value = number_format($value);
                        $td_class = 'text-right';
                    }
                    $str .= '<td class="' . $td_class . '">' . $value . '</td>';
                    break;
            }
        }
        return $str . '</tr>';
    }

    private function prepareFieldName($dataObject, $string = '')
    {
        $arr = explode('.', $string);
        if ($arr) {
            foreach ($arr as $property) {
                $dataObject = property_exists($dataObject, $property) ? $dataObject->{$property} : '';
                continue;
            }
        }
        return $dataObject;
    }

    private function renderTable()
    {
        $this->table = $this->showHeader();
        $this->table .= '<div class="row"><div class="col-xs-12"><table class="table table-bordered table-striped table-hover tableAdmin">';
        $this->table .= $this->th();
        if($this->listRecord) {
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

    private function generateCheckbox(array $attribute = [])
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
    private function generateImage(array $attribute = []) {
        $default = $this->controlDefaultValue();
        $this->extendAttributes($attribute, $default);
        return '<div class="tableAdmin-imageThumb"><img src="'.$default['imagePath'].'"></div>';
    }

    private function defaultOption($label = '', $value = '')
    {
        $label = $label || $this->config['defaultOptionLabel'];
        return '<option value="' . $value . '">' . $label . '</option>';
    }

    private function generateDropdown(array $attribute = [])
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
        return '<div class="list-control-item">
					<select ' . $this->defaultControlAttribute($default) . $default['data-id'] .'>
						' . $opts . '
					</select>
				</div>';
    }

    private function defaultControlAttribute($default)
    {
	    $default['id'] = str_replace(['#','.'],'-',$default['id']);
	    $default['class'] = str_replace(['#','.'],'-',$default['class']);
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
        return [
            'name' => '',
            'label' => '',
            'id' => '',
            'value' => '',
            'title' => '',
            'class' => ''
        ];
    }
}
