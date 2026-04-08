USE [Allocate7]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS (SELECT 1 FROM sys.tables 
			WHERE Name = 'Allocations' )
 BEGIN

	CREATE TABLE Allocations (
	AL_AllocationsID	INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
	AL_WeekNumber	INT,
	AL_SchedulingTeamID	INT,
	AL_Status	INT,
	AL_CreatedBy	INT,
	AL_CreatedDate	DATETIME,
	AL_UpdatedBy	INT,
	AL_UpdatedDate	DATETIME,
	constraint Con_Allocations_CrBy_FK FOREIGN KEY ( AL_CreatedBy ) references UserDetails(UD_UserID),
	constraint Con_Allocations_UpdBy_FK FOREIGN KEY ( AL_UpdatedBy ) references UserDetails(UD_UserID),
	constraint Con_Allocations_ST_FK FOREIGN KEY ( AL_SchedulingTeamID ) references schedulingTeams(schedulingTeamID)
	) ;


	CREATE NONCLUSTERED INDEX idx_Allocations_WeekNumber ON Allocations  (AL_WeekNumber)

	CREATE NONCLUSTERED INDEX idx_Allocations_TeamID ON Allocations  (AL_SchedulingTeamID)

 END

