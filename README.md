# codeigniter-custom
CodeIgniter custom with cli, blade template, my_model...
Save file to folder application/libraries

## Blade
This is a port standalone of Laravel blade template engine.
Docs: https://laravel.com/docs/5.2/blade

## MY_Model
This model was forked from https://github.com/avenirer/CodeIgniter-MY_Model. Support CRUD and trigger function

## MY_Profiler
If you want see profiler like a bottom-float bar, you can try it.
Save file to libraries then enable profiler in config
![Demo](https://kiendt_vn.tinytake.com/sf/NjUzOTYyXzMxNjE1OTM)

## TableAdmin
Generate table listing record in admin
Example:

```
class CategoryController extends MY_Controller {
  ...
  public function index() {
		$allCat = $this->categoryRepository->getAllCategory(0);
		$this->dataView['list'] = $this->parseMultiLevelData($allCat);
		$listIcon = \Solid\Collections\Category::ICON;
		$tableConfig = [
			'module'=>$this->adminModule
		];
		$table = new TableAdmin($this->dataView['list'],$tableConfig);
		$table->column('id','ID');
		$table->columnDropdown('icon','Icon',$listIcon);
		$table->column('active','Active','checkbox');
		$table->column('id','Edit','edit');
		$table->column('id','Delete','delete');

		$this->dataView['tableAdmin'] = $table->render();
		$this->blade->render('admin.category.index',$this->dataView);
	}
```
