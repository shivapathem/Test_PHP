USE [Allocate7]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS (SELECT 1 FROM sys.tables 
			WHERE Name = 'UserRoles' )
 BEGIN

	create table UserRoles (
	UR_UserRoleID	INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
	UR_UserID	INT,
	UR_SchedulingTeamID	INT,
	UR_RoleID	INT,
	UR_StartDate	DATE,
	UR_EndDate	DATE,
	UR_CreatedBy	INT,
	UR_CreatedDate	DATETIME,
	UR_UpdatedBy	INT,
	UR_UpdatedDate	DATETIME,
	constraint Con_UserRoles_UID_FK FOREIGN KEY ( UR_CreatedBy ) references UserDetails(UD_UserID),
	constraint Con_UserRoles_CrBy_FK FOREIGN KEY ( UR_UpdatedBy ) references UserDetails(UD_UserID),
	constraint Con_UserRoles_UpdBy_FK FOREIGN KEY ( UR_UserID ) references UserDetails(UD_UserID),
	constraint Con_UserRoles_Role_FK FOREIGN KEY ( UR_RoleID ) references REF_Roles(RoleID)
	)

	CREATE NONCLUSTERED INDEX idx_UserRoles_UserID  ON  UserRoles
			  (UR_UserID)
	INCLUDE (UR_SchedulingTeamID)

	CREATE NONCLUSTERED INDEX idx_UserRoles_STID  ON  UserRoles
			  (UR_SchedulingTeamID)
	INCLUDE (UR_UserID)

 END