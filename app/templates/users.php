{% extends 'header.php' %}

	{% block pagecontent %}
		<div class="row">
			<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 section-header">
				<h1><i class="fa fa-user header-icon"></i> Users</h1>
				<a class="btn btn-primary pull-right lightbox" href="/users/add" data-action="add user"><i class="fa fa-plus"></i> New User</a>
			</div>
		</div>
		{% if users is not empty %}
		
		<div class="row">
			<div class="col-md-12 col-xs-12">
			
			<table id="trksit-table-users" class="trksit-table table footable" data-filter="#filter" data-page-size="10" data-page-previous-text="prev" data-page-next-text="next" data-page-navigation=".pagination">
			
					<thead>
						<tr>
							<th data-toggle="true" data-sort-ignore="true" class="footable-first-column th email">Email</th>
							<th data-hide="phone,tablet" class="th first-name">First Name</th>
							<th data-hide="phone,tablet" data-sort-initial="ascending" class="th last-name">Last Name</th>
							<th class="th user-type">User Type</th>
							<th data-hide="phone" data-sort-ignore="true" class="footable-last-column th actions text-right">Actions</th>
						</tr>
					</thead>
					<tbody>
						{% for user in users %}
						<tr>
							<td class="footable-first-column">{{ user.email }}</td>
							<td>{{ user.first_name }}</td>
							<td>{{ user.last_name }}</td>
							<td class="user-permissions">{{ user.user_permissions }}</td>
							
							<td class="footable-last-column table-actions text-right"><a href="/users/{{ user.user_id }}" class="action-link"><i class="fa fa-pencil"></i> Edit User</a> <form method="POST" action="/users/{{ user.user_id }}" class="form-inline"> <button type="submit" class="action-link plain-text delete" name="_METHOD" value="DELETE"><i class="fa fa-times-circle"></i> Delete</button></form></td>
						</tr>
						{% endfor %}
					</tbody>
					<tfoot>
		        <tr>
		          <td colspan="6">
		            <div class="pagination pagination-centered hide-if-no-paging"></div>
		          </td>
		        </tr>
		      </tfoot>
				</table>

			
				</div>
		</div>
		{% endif %}
		
		
	{% endblock %}