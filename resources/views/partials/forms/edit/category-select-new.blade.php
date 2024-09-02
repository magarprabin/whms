//add a category
category_html += '<div id="category_id" class="form-group{{ $errors->has('category_id') ? ' has-error' : '' }}">';
category_html += '{{ Form::label('category_id', 'Category 2', array('class' => 'col-md-3 control-label')) }}';
category_html += '<div class="col-md-7{{  ((isset($required)) && ($required=='true')) ? ' required' : '' }}">';
category_html += '<select class="js-data-ajax-add" data-endpoint="categories/{{ (isset($category_type)) ? $category_type : 'assets' }}" data-placeholder="{{ trans('general.select_category') }}" name="category_id" style="width: 100%" id="category_select_id" aria-label="category_id" {!!  ((isset($item)) && (\App\Helpers\Helper::checkIfRequired($item, 'category_id'))) ? ' data-validation="required" required' : '' !!}>';
category_html += '<option value="5" selected="selected" role="option" aria-selected="true"  role="option">';
category_html += '{{ (\App\Models\Category::find('5')) ? \App\Models\Category::find('5')->name : '' }}';
category_html += '</option>';
category_html += '</select>';
category_html += '</div>';
category_html += '</div>';
$(category_field).append(category_html);