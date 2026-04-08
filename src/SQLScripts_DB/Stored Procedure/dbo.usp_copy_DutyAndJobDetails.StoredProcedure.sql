USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_copy_DutyAndJobDetails]    Script Date: 05/09/2024 09:49:14 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER   PROCEDURE [dbo].[usp_copy_DutyAndJobDetails]
	-- Add the parameters for the stored procedure here
	@dutyId				INT,
	@dutyName			VARCHAR(63),
	@dutyType			INT,
	@currentuserid		INT, 
	@currentuser		VARCHAR(200), 
	@history			VARCHAR(2000), 
	@status				TINYINT OUTPUT,
	@returnstring		VARCHAR(1000) OUTPUT
AS
BEGIN
	SET NOCOUNT ON;
	DECLARE @newDutyId INT;
	DECLARE @error INT;
	SET @error			=	0
	SET @status			=	1
	SET @returnstring	=	''
	IF(@dutyId = '' or  @dutyName = '' or @dutyType = '')
	BEGIN
		SET @status			=	0
		SET @returnstring	=	'Invalid input data'
		SELECT @status as returnCode, @returnstring as returnMsg
		return;
	END
	IF EXISTS(SELECT 1 FROM MasterDuties 
	WHERE DutyName = @dutyName AND DutyTypeID = @dutyType AND IsActive = 1 
	AND TeamID = (SELECT TeamID FROM MasterDuties WHERE IsActive = 1 AND DutyTypeID = @dutyType AND MasterDutyID = @dutyId))
	BEGIN
		SET @status			=	0
		SET @returnstring	=	'Duty name already exists'
		SELECT @status as returnCode, @returnstring as returnMsg
		return;
	END
	BEGIN TRANSACTION
	if (EXISTS(select 1 from MasterDuties where IsActive = 1 and DutyTypeID = @dutyType and MasterDutyID = @dutyId))
	-- and exists(select 1 from MasterDutiesMasterJobs_LINK where MasterDutyID = @dutyId)
	BEGIN
		insert into MasterDuties(AreaID ,TeamID ,DutyTypeID ,DutyName ,DutyColourID ,AutoShrink ,Duration ,
		WindowDuration ,IsPaid ,Saturday ,Sunday ,Monday ,Tuesday ,Wednesday ,
		Thursday ,Friday ,StartWeek ,StartDate ,EndWeek ,EndDate ,StartTime ,EndTime ,dotw ,IsActive ,
		CreatedBy ,CreatedDate ,LastModBy ,LastModDate ,History ,BackColour ,
		ForeColour ,BaseCode ,LegacyId ,BreakTime ,DepartmentId ,SystemId,
		DutyProgramId1, DutyProgramId2, DutyProgramId3, DutyProgramId4,
		DutyProgramId5, DutyProgramId6,IsNeedCovering )
		select AreaID ,TeamID ,DutyTypeID ,@dutyName ,DutyColourID ,AutoShrink ,Duration ,WindowDuration ,IsPaid ,Saturday ,
		Sunday ,Monday ,Tuesday ,Wednesday ,Thursday ,
		Friday ,StartWeek ,StartDate ,EndWeek ,EndDate ,StartTime ,EndTime ,dotw ,IsActive ,@currentuserid ,GETDATE() ,
		@currentuserid ,GETDATE() ,@history ,BackColour ,ForeColour ,
		BaseCode ,LegacyId ,BreakTime ,DepartmentId ,SystemId,
		DutyProgramId1, DutyProgramId2, DutyProgramId3, DutyProgramId4,
		DutyProgramId5, DutyProgramId6,IsNeedCovering
		from MasterDuties
		where MasterDutyID = @dutyId 
		  and IsActive = 1 
		  and DutyTypeID = @dutyType

		SELECT @newDutyId = SCOPE_IDENTITY(), @error = @@ERROR
		if (@error <> 0)
		BEGIN
			SET @status			=	0
			SET @returnstring	=	'There was a problem in coping the Duty'
			ROLLBACK TRANSACTION
		END
		ELSE
		BEGIN

		/*	INSERT INTO LINK_MasterDuties_Lables(MasterDutyId, LabelId, IsActive) 
			select @newDutyId, LabelId,1 from LINK_MasterDuties_Lables where MasterDutyId = @dutyId and isActive = 1  */

			INSERT INTO MasterDutiesMasterJobs_LINK (MasterDutyID, MasterJobID, IsMidNight)
			SELECT @newDutyId, MasterJobID, IsMidNight FROM MasterDutiesMasterJobs_LINK WHERE MasterDutyID = @dutyId
			
			SELECT @error = @@ERROR

		/*	INSERT INTO MasterDutyTeams (DutyId, TeamID, IsActive, CreatedBy, CreatedDate, LastModBy, LastModDate, History)
			SELECT @newDutyId, TeamID, IsActive, CreatedBy, CreatedDate, LastModBy, LastModDate, History 
			FROM MasterDutyTeams WHERE IsActive = 1 and DutyID = @dutyId  */
			if (@error <> 0 or @@ERROR <> 0)
			BEGIN
				SET @status			=	0
				SET @returnstring	=	'There was a problem in coping the Duty'
				ROLLBACK TRANSACTION
			END
			ELSE
			BEGIN
				SET @status			=	1
				SET @returnstring	=	CONCAT('Duty has been copied. New duty id is ', @newDutyId)
			END
		END
	END
	ELSE
	BEGIN
		SET @status			=	0
		SET @returnstring	=	'Master duty not found'
	END
	COMMIT TRANSACTION
	SELECT @status as returnCode, @returnstring as returnMsg
END
