The Kbelští sokoli system uses the RBAC (Role-Based Access Control) model. This means that access to individual functions in the administration and mobile application is controlled by assigned roles.

### Main System Roles
1. **Superadmin**: Has absolute access to everything in the system, including sensitive settings, logs, and management of the roles themselves.
2. **Administrator**: The main role for club management. They can manage members, finances, teams, and website content, but do not have access to the most sensitive technical configurations.
3. **Coach**: A role focused on sporting activities. They see their teams, training sessions, attendance, and matches. They cannot edit global settings or the finances of other members.
4. **Editor**: A specialized role for content management. They can write news, upload photo galleries, and edit static pages on the website.
5. **User (Player/Parent)**: The basic role that every member has. It allows access to the member section for managing their own profile, payments, and attendance.

### How to Assign a Role to a Member?
Role assignment takes place in the member detail (People and Members > Users).
1. Search for the user and open their edit page.
2. Go to the **Security** tab (or Admin depending on configuration).
3. In the **Roles** field, select the desired role from the list (e.g., "Coach").
4. Save the changes with the **Save changes** button.
*Note: A user can have multiple roles simultaneously (e.g., Coach and Editor).*

### Difference between System Role and Team Role
- **System Role** (this section): Determines what the user sees in the administration menu and what their global rights are.
- **Team Role** (in Season Configuration): Determines only the relationship to the team (e.g., Captain, Goalkeeper). It has no effect on access to administration.

### Advanced Management (Superadmin)
In the **System Settings > Roles and Permissions** section, superadministrators can define fine-grained permissions for individual roles.
- **Warning**: Changing permissions for basic roles (such as Coach or Admin) can affect the functionality of the entire system. Make these changes only with a full understanding of the impacts.
