USE [Allocate7]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

IF NOT EXISTS (SELECT 1 FROM sys.tables 
			WHERE Name = 'AllocationsAddPersons_DEL' )
 BEGIN


		create table AllocationsAddPersons_DEL (
			AAP_AllocationsAPID INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
			AAP_ORIG_AllocationsAPID INT,
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
			constraint Con_AllocationsAddPersons_DEL_SPID_FK FOREIGN KEY ( AAP_SchedulingPersonID ) references UserDetails(UD_UserID),
			constraint Con_AllocationsAddPersons_DEL_UID_FK FOREIGN KEY ( AAP_CreatedBy ) references UserDetails(UD_UserID),
			constraint Con_AllocationsAddPersons_DEL_CrBy_FK FOREIGN KEY ( AAP_UpdatedBy ) references UserDetails(UD_UserID),
			constraint Con_AllocationsAddPersons_DEL_UpdBy_FK FOREIGN KEY ( AAP_AllocationsID ) references Allocations_DEL(AL_AllocationsID) )

		CREATE NONCLUSTERED INDEX idx_AllocationsAddPersons_DEL_AllocationsID  ON  AllocationsAddPersons_DEL
				  (AAP_AllocationsID) 	  

		CREATE NONCLUSTERED INDEX idx_AllocationsDuties_DEL_SPID  ON  AllocationsAddPersons_DEL
				  ( AAP_AllocationsSPID)

 END				  