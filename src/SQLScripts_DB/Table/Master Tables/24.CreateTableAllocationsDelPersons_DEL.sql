USE [Allocate7]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS (SELECT 1 FROM sys.tables 
			WHERE Name = 'AllocationsDelPersons' )
 BEGIN


	create table AllocationsDelPersons_DEL (
	ADP_AllocationsADPID	INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
	ADP_AllocationsID		INT,
	ADP_SchedulingPersonID	INT,
	ADP_Status				INT,
	ADP_CreatedBy			INT,
	ADP_CreatedDate			DATETIME,
	ADP_UpdatedBy			INT,
	ADP_UpdatedDate			DATETIME,
	constraint Con_AllocationsDelPersonsDel_SPID_FK FOREIGN KEY ( ADP_SchedulingPersonID ) references UserDetails(UD_UserID),
	constraint Con_AllocationsDelPersonsDel_UID_FK FOREIGN KEY ( ADP_CreatedBy ) references UserDetails(UD_UserID),
	constraint Con_AllocationsDelPersonsDel_CrBy_FK FOREIGN KEY ( ADP_UpdatedBy ) references UserDetails(UD_UserID),
	constraint Con_AllocationsDelPersonsDel_UpdBy_FK FOREIGN KEY ( ADP_AllocationsID ) references Allocations(AL_AllocationsID) )

	CREATE NONCLUSTERED INDEX idx_AllocationsDelPersonsDel_AllocationsID  ON  AllocationsDelPersons
			  (ADP_AllocationsID) 
			INCLUDE 
			(  ADP_SchedulingPersonID)
			

			  
 END			  