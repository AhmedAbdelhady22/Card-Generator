# User Permission Management System

## Overview
This system provides granular control over user permissions, allowing administrators to grant or deny specific permissions to individual users beyond their role-based permissions.

## Features

### 1. **Individual User Permissions**
- Grant specific permissions to users regardless of their role
- Deny specific permissions to users even if their role has them
- Permissions hierarchy: Denied > Individual > Role

### 2. **Admin Interface**
- View all users and their current permissions
- Visual indicators for role permissions, granted permissions, and denied permissions
- Easy-to-use interface for managing permissions
- Conflict prevention (cannot grant and deny the same permission)

### 3. **Permission-Based Navigation**
- Navigation menu adapts based on user's actual permissions
- Real-time updates when permissions change
- Secure access control throughout the application

## How to Use

### Accessing Permission Management
1. Log in as an administrator
2. Navigate to **Admin Panel** → **Users**
3. Click **Manage Permissions** for any user

### Managing Permissions
- **Role Permissions**: Displayed in light badges (inherited from user's role)
- **Grant Permission**: Check the green checkbox to give additional permissions
- **Deny Permission**: Check the red checkbox to revoke permissions
- Click **Update Permissions** to save changes

### Permission Types
- `view_cards` - View card listings
- `create_cards` - Create new cards
- `edit_cards` - Edit existing cards
- `download_pdf` - Download PDF versions
- `study_cards` - Access study features
- `manage_users` - Manage other users
- `manage_permissions` - Manage user permissions
- `view_admin_panel` - Access admin dashboard
- `view_activity_logs` - View system activity logs

## Technical Details

### Database Structure
- `user_permissions` - Stores granted individual permissions
- `user_denied_permissions` - Stores denied individual permissions
- Maintains referential integrity with users and permissions tables

### Permission Priority
1. **Denied Permissions** (highest priority) - Always blocks access
2. **Individual Granted Permissions** - Overrides role permissions
3. **Role Permissions** (lowest priority) - Default permissions from user's role

### Security
- All admin routes protected by permission-based middleware
- User permissions loaded with relationships for performance
- Activity logging for all permission changes

## Installation Notes
- All database migrations are included
- Permission seeder creates default permissions
- No additional configuration required
- Compatible with existing role-based system

## Support
The system is fully integrated with the existing Laravel application and follows Laravel best practices for security and performance.
