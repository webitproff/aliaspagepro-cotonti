<!--
	/**
	* aliaspagepro.admin.tpl 
	*
	* aliaspagepro plugin for Cotonti v1+, PHP 8.5+
	* Filename: plugins/aliaspagepro/tpl/aliaspagepro.admin.tpl
	*
	*
	* ReadMeMore:       https://abuyfile.com/ru/market/cotonti/plugs/aliaspagepro
	* Support:          https://abuyfile.com/forums/cotonti/custom/plugs/
	* Source:           https://github.com/webitproff/aliaspagepro-cotonti
	* 
	* Date: Aug 6Th, 2026
	*
	* @package Alias Page PRO
	* @version 2.5.1
	* @author webitproff
	* @copyright (c) webitproff 2026
	* @license BSD
	*/
	
	
-->
<!-- BEGIN: MAIN -->
<div class="container-fluid py-4">
    <h2>{PHP.L.aliaspagepro_name}</h2>
    {FILE "{PHP.cfg.themes_dir}/{PHP.cfg.defaulttheme}/warnings.tpl"}
	
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link {TAB_STATS_ACTIVE}" href="{URL_STATS}">
                {PHP.L.aliaspagepro_tab_stats}
			</a>
		</li>
        <li class="nav-item">
            <a class="nav-link {TAB_LIST_ACTIVE}" href="{URL_LIST}">
                {PHP.L.aliaspagepro_tab_list}
			</a>
		</li>
        <li class="nav-item">
            <a class="nav-link {TAB_MANAGE_ACTIVE}" href="{URL_MANAGE}">
                {PHP.L.aliaspagepro_tab_manage}
			</a>
		</li>
	</ul>
	
    <!-- STATISTICS TAB -->
    <!-- IF {PHP.tab} == 'stats' -->
    <div class="row">
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">{STATS_TOTAL_ITEMS}</h5>
                    <p class="card-text">{PHP.L.aliaspagepro_stats_total_items}</p>
				</div>
			</div>
		</div>
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">{STATS_WITH_ALIAS}</h5>
                    <p class="card-text">{PHP.L.aliaspagepro_stats_with_alias}</p>
				</div>
			</div>
		</div>
        <div class="col-md-3">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">{STATS_WITHOUT_ALIAS}</h5>
                    <p class="card-text">{PHP.L.aliaspagepro_stats_without_alias}</p>
				</div>
			</div>
		</div>
        <div class="col-md-3">
            <div class="card text-white bg-danger mb-3">
                <div class="card-body">
                    <h5 class="card-title">{STATS_WITHOUT_METATITLE}</h5>
                    <p class="card-text">{PHP.L.aliaspagepro_stats_without_metatitle}</p>
				</div>
			</div>
		</div>
	</div>
	
    <div class="mt-4">
        <a href="{AUTOALIAS_CREATE_URL}" class="btn btn-primary">
            {PHP.L.create_aliases}
		</a>
        <a href="{PHP|cot_url('admin', 'm=config&n=edit&o=plug&p=aliaspagepro')}" class="btn btn-secondary">
            {PHP.L.Configuration}
		</a>
		<a href="{PHP|cot_url('admin', 'm=other&p=aliaspagepro&a=clear_aliases&tab=stats')}" class="btn btn-danger" onclick="return confirm('{PHP.L.aliaspagepro_confirm_clear}')">
			{PHP.L.aliaspagepro_clear_all}
		</a>
	</div>
    <!-- ENDIF -->
	
    <!-- LISTS & ALIASES TAB -->
    <!-- IF {PHP.tab} == 'list' -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th style="width:60px">ID</th>
                    <th>{PHP.L.aliaspagepro_col_title}</th>
                    <th>{PHP.L.aliaspagepro_col_metatitle}</th>
                    <th>{PHP.L.aliaspagepro_col_alias}</th>
				</tr>
			</thead>
            <tbody>
                <!-- BEGIN: LIST_ROW -->
                <tr>
                    <td>{LIST_ROW_ID}</td>
                    <td><a href="{LIST_ROW_TITLE_URL}" target="_blank">{LIST_ROW_TITLE}</a></td>
                    <td>{LIST_ROW_METATITLE}</td>
                    <td>{LIST_ROW_ALIAS}</td>
				</tr>
                <!-- END: LIST_ROW -->
                <!-- BEGIN: LIST_EMPTY -->
                <tr><td colspan="4" class="text-center">{PHP.L.aliaspagepro_no_items}</td></tr>
                <!-- END: LIST_EMPTY -->
			</tbody>
		</table>
	</div>
	<!-- IF {PAGINATION} -->
	<nav aria-label="Page Pagination" class="mt-3">
		<div class="text-center mb-2">{PHP.L.Total}: {TOTAL_ENTRIES}, {PHP.L.Onpage}: {ENTRIES_ON_CURRENT_PAGE}</div>
		<ul class="pagination justify-content-center">{PREVIOUS_PAGE} {PAGINATION} {NEXT_PAGE}</ul>
	</nav>
	<!-- ENDIF -->
    <!-- ENDIF -->
	
    <!-- MANAGEMENT TAB -->
    <!-- IF {PHP.tab} == 'manage' -->
    <form method="post" action="{MANAGE_FORM_URL}">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th style="width:60px">ID</th>
                        <th>{PHP.L.aliaspagepro_col_title}</th>
                        <th>{PHP.L.aliaspagepro_col_metatitle}</th>
                        <th>{PHP.L.aliaspagepro_col_alias}</th>
					</tr>
				</thead>
                <tbody>
                    <!-- BEGIN: MANAGE_ROW -->
                    <tr>
                        <td class="align-middle">{MANAGE_ID}<input type="hidden" name="ids[]" value="{MANAGE_ID}"></td>
                        <td><input type="text" name="title[{MANAGE_ID}]" value="{MANAGE_TITLE}" class="form-control"></td>
                        <td><input type="text" name="metatitle[{MANAGE_ID}]" value="{MANAGE_METATITLE}" class="form-control"></td>
                        <td><input type="text" name="alias[{MANAGE_ID}]" value="{MANAGE_ALIAS}" class="form-control"></td>
					</tr>
                    <!-- END: MANAGE_ROW -->
                    <!-- BEGIN: MANAGE_EMPTY -->
                    <tr><td colspan="4" class="text-center">{PHP.L.aliaspagepro_no_items}</td></tr>
                    <!-- END: MANAGE_EMPTY -->
				</tbody>
			</table>
		</div>
        <button type="submit" class="btn btn-success">{PHP.L.Update}</button>
	</form>
	<!-- IF {PAGINATION} -->
	<nav aria-label="Page Pagination" class="mt-3">
		<div class="text-center mb-2">{PHP.L.Total}: {TOTAL_ENTRIES}, {PHP.L.Onpage}: {ENTRIES_ON_CURRENT_PAGE}</div>
		<ul class="pagination justify-content-center">{PREVIOUS_PAGE} {PAGINATION} {NEXT_PAGE}</ul>
	</nav>
	<!-- ENDIF -->
    <!-- ENDIF -->
</div>
<!-- END: MAIN -->