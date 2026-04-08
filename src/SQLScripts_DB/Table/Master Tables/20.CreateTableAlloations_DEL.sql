USE [Allocate7]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS (SELECT 1 FROM sys.tables 
			WHERE Name = 'Allocations_DEL' )
 BEGIN


	Create table Allocations_DEL (
	AL_AllocationsID	INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
	AL_ORIG_AllocationsID	INT,
	AL_WeekNumber	INT,
	AL_SchedulingTeamID	INT,
	AL_Status	INT,
	AL_CreatedBy	INT,
	AL_CreatedDate	DATETIME,
	AL_UpdatedBy	INT,
	AL_UpdatedDate	DATETIME,
	constraint Con_Allocations_DEL_CrBy_FK FOREIGN KEY ( AL_CreatedBy ) references UserDetails(UD_UserID),
	constraint Con_Allocations_DEL_UpdBy_FK FOREIGN KEY ( AL_UpdatedBy ) references UserDetails(UD_UserID),
	constraint Con_Allocations_DEL_ST_FK FOREIGN KEY ( AL_SchedulingTeamID ) references schedulingTeams(schedulingTeamID),
	constraint Con_Allocations_DEL_ALID_FK FOREIGN KEY ( AL_ORIG_AllocationsID ) references Allocations(AL_AllocationsID)
	) ;

	CREATE NONCLUSTERED INDEX idx_Allocations_DEL_WeekNumber ON Allocations_DEL  (AL_WeekNumber)

	CREATE NONCLUSTERED INDEX idx_Allocations_DEL_TeamID ON Allocations_DEL  (AL_SchedulingTeamID)
	
 END

