<?php

/**
 * Util class for page iterators
 *
 * Condition: querystring param to iterate pages must be named "p"
 *
 */
class ThefIterator
{

    private $rpp = 10;			// records to show per page
    private $total_records;		// total number of db records
    private $begin = 1;			// number of the begining page
    private $max_index = 5;		// maximum of page numbers to show after prev/next
    private $rstart = 0;		// value of the starting record
    private $a_href = 'javascript:;';	// link function and {p} variable -> ie: http://www.site.com?module.php?page={p}
    private $on_click = '';		// onclick action
    private $template = 'html/tpl/common/iterator.html';    // html template
    private $show_more_info = true;	// show extended info (ie: showing records X of Y)
    private $p = 1;			// current page
    private $record_ini = 0;		// first index to show results
    private $css_selected = 'active';	// css class to selected page
    private $prev_next_always = false;	// true if prev and next buttons always appears
    private $prev_next_single = false;	// true if prev/next buttons moves for 1 page at time
    private $show_first_last = true;	// true if shows first and last page

    public function __construct()
    {
	// PATCH FOR HTACCESS
	$arr_1 = explode('?p=', $_SERVER['REQUEST_URI']);
	$arr_2 = explode('&p=', $_SERVER['REQUEST_URI']);
	$p = max(end($arr_1), end($arr_2));

	if (is_numeric($p))
	    $_REQUEST['p'] = $p;

	$this->p = addslashes($_REQUEST['p']);
	if (!is_numeric($this->p))
	    $this->p = 1;
    }



    /**
     * Manually set the current page
     * @param Integer $value Current page
     */
    public function setP($value)
    {
	$this->p = $value;
    }



    /**
     * Set records per page (default: 10)
     * @param Integer $value records per page
     */
    public function setRecordsPage($value)
    {
	$this->rpp = $value;
    }



    /**
     * Set total records
     * @param Integer $value Total of records
     */
    public function setTotalRecords($value)
    {
	$this->total_records = $value;
    }



    /**
     * Set the max index to show
     * @param Integer $value Max index
     */
    public function setMaxPages($value)
    {
	$this->max_index = $value;
    }



    /**
     * Set the href url to show in each page link ({p} will be replaced with the page number)
     * This method will override the onclick function
     * @param String $value href url for each page
     */
    public function setAHREF($value)
    {
	$this->on_click = '';
	$this->a_href = $value;
    }



    /**
     * Set the onclick function to call in each page link ({p} will be replaced with the page number)
     * This method will override the href url
     * @param String $value onclick function
     */
    public function setOnClick($value)
    {
	$this->on_click = $value;
	$this->a_href = 'javascript:;';
    }



    /**
     * Set the css class for the selected page
     * @param String $value css class name
     */
    public function setClassSelected($value)
    {
	$this->css_selected = $value;
    }



    /**
     * Set if prev/next buttons show prev/next page (true) or iteretate by max_index
     * @param Boolean $value true = 1, false = max_index
     */
    public function setPrevNextSingle($value)
    {
	$this->prev_next_single = $value;
    }



    /**
     * Set if prev/next buttons are always visible
     * @param Boolean $value true if buttons are always visible
     */
    public function setPrevNextAlways($value)
    {
	$this->prev_next_always = $value;
    }



    /**
     * Set if more info is available (more if is "Showing page X of Y, total records = Z")
     * @param Boolean $value true to show more information
     */
    public function showMoreInfo($value)
    {
	$this->show_more_info = $value;
    }



    /**
     * Set if there is a link to the first and last page
     * @param Boolean $value true to show links
     */
    public function showFirstLast($value)
    {
	$this->show_first_last = $value;
    }



    /**
     * Set a html template to use
     * @param String $tpl Relative path of html file
     */
    public function setTemplate($tpl)
    {
	$this->template = $tpl;
    }



    /**
     * Return the HTML code for the iterator
     * @return String HTML code
     */
    public function getHTML()
    {
	if ($this->total_records > 0) {

	    $oTemplate = new ThefTemplate(ROOT_PATH . $this->template, '');

	    // number of total pages
	    $total_pages = ceil($this->total_records / $this->rpp);
	    // number of the last record in this page
	    $pag_end = min(($this->begin * $this->rpp - 1), $total_pages);
	    // number of the first link
	    $starting = max(1, $this->begin - $this->max_index);
	    // number of the last link
	    $ending = min($total_pages, $this->begin + $this->max_index - 1);

	    // number of the starting record
	    if ($this->begin == 1) {
		$this->rstart = 0;
	    } else {
		$this->rstart = ($this->begin - 1) * $this->rpp;
	    }

	    // FIRST BUTTON
	    if ($this->show_first_last && ($total_pages > 1) && ($this->p > 1)) {
		$oTemplate->newBlock('FIRST');
		$oTemplate->assign('href', str_replace('{p}', 1, $this->a_href));
		$oTemplate->assign('onclick', str_replace('{p}', 1, $this->on_click));
	    }

	    // PREV BUTTON
	    $init_iter = max(1, min($total_pages - $this->max_index + 1, ceil($this->p - ($this->max_index / 2))));
	    if ($this->prev_next_single) {
		$prev = max(1, $this->p - 1);
		if ($this->p > 1 || $this->prev_next_always) {
		    $oTemplate->newBlock('PREV');
		    $oTemplate->assign('href', str_replace('{p}', $prev, $this->a_href));
		    $oTemplate->assign('onclick', str_replace('{p}', $prev, $this->on_click));
		    if ($this->p == 1)
			$oTemplate->assign('hidden', 'hidden');
		}
	    } else {
		$prev = max(1, $init_iter - 1);
		if ($init_iter > 1 || $this->prev_next_always) {
		    $oTemplate->newBlock('PREV');
		    $oTemplate->assign('href', str_replace('{p}', $prev, $this->a_href));
		    $oTemplate->assign('onclick', str_replace('{p}', $prev, $this->on_click));
		    if ($init_iter == 1)
			$oTemplate->assign('hidden', 'hidden');
		}
	    }

	    // NEXT BUTTON
	    $final_iter = min($total_pages, $init_iter + $this->max_index - 1);
	    if ($this->prev_next_single) {
		$next = min($total_pages, $this->p + 1);
		if ($this->p < $total_pages || $this->prev_next_always) {
		    $oTemplate->newBlock('NEXT');
		    $oTemplate->assign('href', str_replace('{p}', $next, $this->a_href));
		    $oTemplate->assign('onclick', str_replace('{p}', $next, $this->on_click));
		    if ($this->p == $total_pages)
			$oTemplate->assign('hidden', 'hidden');
		}
	    } else {
		$next = min($final_iter + 1, $total_pages);
		if ($final_iter < $total_pages || $this->prev_next_always) {
		    $oTemplate->newBlock('NEXT');
		    $oTemplate->assign('href', str_replace('{p}', $next, $this->a_href));
		    $oTemplate->assign('onclick', str_replace('{p}', $next, $this->on_click));
		    if ($final_iter == $total_pages)
			$oTemplate->assign('hidden', 'hidden');
		}
	    }

	    if ($init_iter != $final_iter || $this->prev_next_always) {
		for ($i = $init_iter; $i <= $final_iter; $i++) {
		    $oTemplate->newBlock('PAGE');
		    $class = ($i == $this->p) ? $this->css_selected : '';
		    $oTemplate->assign('class', $class);
		    $oTemplate->assign('href', str_replace('{p}', $i, $this->a_href));
		    $oTemplate->assign('onclick', str_replace('{p}', $i, $this->on_click));
		    $oTemplate->assign('p', $i);
		}
	    }

	    // LAST BUTTON
	    if ($this->show_first_last && ($total_pages > 1) && ($this->p < $total_pages)) {
		$oTemplate->newBlock('LAST');
		$oTemplate->assign('href', str_replace('{p}', $total_pages, $this->a_href));
		$oTemplate->assign('onclick', str_replace('{p}', $total_pages, $this->on_click));
	    }

	    if ($this->show_more_info) {
		$oTemplate->newBlock('MORE_INFO');
		$oTemplate->assign('current_records', $this->p);
		$oTemplate->assign('current_page', $this->p);
		$oTemplate->assign('total_pages', $total_pages);
		$oTemplate->assign('total_records', $this->total_records);
	    }

	    return $oTemplate->getHTML();
	} else {
	    return '';
	}
    }



    /**
     * Return LIMIT X,Y sentence for current query
     * @return String HTML code
     */
    public function getLimit()
    {
	$limit = '';
	if ($this->rpp > 0) {
	    $ini = max(0, ($this->p - 1) * $this->rpp);
	    $limit = " LIMIT $ini, " . $this->rpp;
	}
	return $limit;
    }



}
