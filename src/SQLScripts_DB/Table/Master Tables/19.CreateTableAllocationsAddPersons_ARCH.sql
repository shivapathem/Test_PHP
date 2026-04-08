USE [Allocate7]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS (SELECT 1 FROM sys.tables 
			WHERE Name = 'AllocationsScheduledPersons_ARCH' )
 BEGIN


	create table AllocationsAddPersons_ARCH (
	AAP_AllocationsAPID INT NOT NULL PRIMARY KEY,
	AAP_AllocationsID	INT,
	AAP_AllocationsSPID	INT,
	AAP_SchedulingPersonID INT,
	AAP_Status	INT,
	AAP_iDay INT,
	AAP_DutyDate DATE,
	AAP_CreatedBy	INT,
	AAP_CreatedDate	DATETIME,
	AAP_UpdatedBy	INT,
	AAP_UpdatedDate	DATETIME,
	constraint Con_AllocationsAddPersons_ARCH_SPID_FK FOREIGN KEY ( AAP_SchedulingPersonID ) references UserDetails(UD_UserID),
	constraint Con_AllocationsAddPersons_ARCH_UID_FK FOREIGN KEY ( AAP_CreatedBy ) references UserDetails(UD_UserID),
	constraint Con_AllocationsAddPersons_ARCH_CrBy_FK FOREIGN KEY ( AAP_UpdatedBy ) references UserDetails(UD_UserID),
	constraint Con_AllocationsAddPersons_ARCH_UpdBy_FK FOREIGN KEY ( AAP_AllocationsID ) references Allocations_ARCH(AL_AllocationsID),
	constraint Con_AllocationsAddPersons_ARCH_UpdBy_FK FOREIGN KEY ( AAP_AllocationsSPID ) references AllocationsScheduledPersons_ARCH(ASP_AllocationsSPID) )

	CREATE NONCLUSTERED INDEX idx_AllocationsAddPersons_ARCH_AllocationsID  ON  AllocationsAddPersons_ARCH
			  (AAP_AllocationsID) 	  

	CREATE NONCLUSTERED INDEX idx_AllocationsDuties_ARCH_SPID  ON  AllocationsAddPersons_ARCH
			  ( AAP_AllocationsSPID)
			  
 END		  