USE [Allocate7]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS (SELECT 1 FROM sys.tables 
			WHERE Name = 'Allocations_ARCH' )
 BEGIN

	Create table Allocations_ARCH (
	AL_AllocationsID	INT NOT NULL PRIMARY KEY,
	AL_WeekNumber	INT,
	AL_SchedulingTeamID	INT,
	AL_Status	INT,
	AL_CreatedBy	INT,
	AL_CreatedDate	DATETIME,
	AL_UpdatedBy	INT,
	AL_UpdatedDate	DATETIME,
	AL_UpdatedByInRed varchar(100)
	constraint Con_Allocations_ARCH_CrBy_FK FOREIGN KEY ( AL_CreatedBy ) references UserDetails(UD_UserID),
	constraint Con_Allocations_ARCH_UpdBy_FK FOREIGN KEY ( AL_UpdatedBy ) references UserDetails(UD_UserID),
	constraint Con_Allocations_ARCH_ST_FK FOREIGN KEY ( AL_SchedulingTeamID ) references schedulingTeams(schedulingTeamID)
	) ;


	CREATE NONCLUSTERED INDEX idx_Allocations_ARCH_WeekNumber ON Allocations_ARCH  (AL_WeekNumber)

	CREATE NONCLUSTERED INDEX idx_Allocations_ARCH_TeamID ON Allocations_ARCH  (AL_SchedulingTeamID)
	
 END

