USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_mod_MiscellaneousDutyDetails]    Script Date: 29/07/2024 18:47:03 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER  PROCEDURE [dbo].[usp_mod_MiscellaneousDutyDetails]
@dutyid				INT,
@dutyname			VARCHAR(60),
@teamid				INT,
@areaid				INT,
@dutytypeid			INT,
@duration			INT,
@dutycolourid		INT,
@currentuserid		INT,
@currentuser		VARCHAR(200),
@history			VARCHAR(2000),
@status				TINYINT OUTPUT,
@returnstring		VARCHAR(1000) OUTPUT,
@BreakTime			INT,
@LabelId			INT,
@IsNightShift       BIT = 0,
@LabelID2           INT,
@LabelID3           INT,
@LabelID4           INT,
@LabelID5           INT,
@LabelID6           INT

AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

	DECLARE @err int
	DECLARE @rows int
	DECLARE @RowNum int
	DECLARE @UpdateDateTime datetime
	DECLARE @strUpdateDateTime varchar(35)
	DECLARE @TmpHistory varchar(4000)
	DECLARE @DOTW varchar(100)
	DECLARE @isactive int
	DECLARE @newdutyid int
	DECLARE @oldteamid int
	DECLARE @RowNum1 int
	DECLARE @startdate VARCHAR(60)
	DECLARE @enddate	VARCHAR(60)
	DECLARE @startweek int;
	declare @endweek INT,
	        @prevLabelId INT,
			@prevLabelName NVARCHAR(50),
			@prevLabelId2 INT, @prevLabelId3 INT,@prevLabelId4 INT,@prevLabelId5 INT, @prevLabelId6 INT,
	    @prevLabelName2 NVARCHAR(50), @prevLabelName3 NVARCHAR(50), @prevLabelName4 NVARCHAR(50),
		@prevLabelName5 NVARCHAR(50), @prevLabelName6 NVARCHAR(50);;

	SET @DOTW = ''
	SET @status = 1
	SET @returnstring = 'success'
	SET @UpdateDateTime = GETDATE()
	SET @strUpdateDateTime = CONVERT(VARCHAR, @UpdateDateTime, 106) + ' at ' + CONVERT(VARCHAR, @UpdateDateTime, 108)

	SET @DOTW = '0,1,2,3,4,5,6';--validate breaktime
	if(@duration < @BreakTime)
	BEGIN
		SET @returnstring = 'Break time should not be greater than duration.';
		SET @status = 0;
		select @status intStatus,@returnstring strStatus, 0 NewMiscDutyId;
		RETURN;
	END
	-- CHECK Name available
	IF EXISTS(SELECT 1 
	           FROM MasterDuties 
	          WHERE MasterDutyID != @dutyid 
			    and DutyName = @dutyname 
				and IsActive = 1 
				and DutyTypeID != 1 
				and TeamID = @teamid)
	BEGIN
		SET @returnstring = 'Miscellaneous Duty Name already used.';
		SET @status = 0;
		select @status intStatus,@returnstring strStatus, 0 NewMiscDutyId;
		RETURN;
	END

	IF (@dutyid = 0)
	BEGIN
		IF EXISTS(SELECT 1 
		           FROM MasterDuties 
				  WHERE (MasterDutyID = @dutyid) 
				    and IsActive = 1 
					and TeamID = @teamid)
		BEGIN
			SET @returnstring = 'Miscellaneous Duty Id already exists.';
			SET @status = 0;
			select @status intStatus,@returnstring strStatus, 0 NewMiscDutyId;
			RETURN;
		END

		--Insert new MasterDuties record
		INSERT INTO MasterDuties(AreaID,TeamID,DutyTypeID,DutyName,DutyColourID,BackColour,
		   ForeColour,Duration,WindowDuration,IsPaid,Saturday,
			Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,StartTime,EndTime,dotw,IsActive,CreatedBy,
			CreatedDate,LastModBy,LastModDate,History, BreakTime, IsNightShift,
			DutyProgramId1,DutyProgramId2,DutyProgramId3,DutyProgramId4,DutyProgramId5,DutyProgramId6)
		VALUES (@areaid,@teamid,@dutytypeid,@dutyname,@dutycolourid,0,0,@duration,@duration,1,1,1,1,1,
		1,1,1,0,0,@DOTW,1,@currentuserid,@UpdateDateTime,
		@currentuserid,@UpdateDateTime,@history, @BreakTime,@IsNightShift,
		@LabelId, @LabelID2, @LabelID3, @LabelID4, @LabelID5, @LabelID6)

		-- get last insert data
		SET @newdutyid = Scope_Identity();
		SET @isactive = 1;

		/*IF ( ISNULL(@LabelId,0) > 0 ) 
		 BEGIN
		  INSERT INTO LINK_MasterDuties_Lables(MasterDutyId, LabelId, IsActive) 
		  VALUES (@newdutyid, @LabelId, @isactive)
		 END

		--Insert new MasterTeam record
		INSERT INTO MasterDutyTeams(DutyID,TeamID,IsActive,CreatedBy,CreatedDate,LastModBy,LastModDate,History)
		VALUES (@newdutyid,@teamid,@isactive,@currentuserid, @UpdateDateTime, @currentuserid, @UpdateDateTime,@history) */

		SELECT @err = @@ERROR, @rows = @@ROWCOUNT
		IF @err <> 0
		BEGIN
			SET @returnstring = @err;
			SET @status = 0;
			select @status intStatus,@returnstring strStatus, 0 NewMiscDutyId;
			RETURN;
		END
		IF @rows = 0
		BEGIN
			SET @returnstring = 'There is some error while inserting miscellaneous duty team record. Please contact Administrator.';
			SET @status = 0;
			select @status intStatus,@returnstring strStatus, 0 NewMiscDutyId;
			RETURN;
		END
		-------
		select @status intStatus,'Miscellaneous duty record inserted successfully.' strStatus, @newdutyid NewMiscDutyId;
		return;
	END
	ELSE
	BEGIN

		SELECT 
			@prevLabelId = isNull(DutyProgramId1, ''), 
			@prevLabelId2 = isNull(DutyProgramId2, ''), 
			@prevLabelId3 = isNull(DutyProgramId3, ''), 
			@prevLabelId4 = isNull(DutyProgramId4, ''), 
			@prevLabelId5 = isNull(DutyProgramId5, ''), 
			@prevLabelId6 = isNull(DutyProgramId6, ''), 
			@prevLabelName =  PG.Programme, 
			@prevLabelName2 =  PG2.Programme,
			@prevLabelName3 =  PG3.Programme,
			@prevLabelName4 =  PG4.Programme,
			@prevLabelName5 =  PG5.Programme,
			@prevLabelName6 =  PG6.Programme
		FROM MasterDuties MD
		LEFT JOIN Programmes PG ON PG.ID = MD.DutyProgramId1
		LEFT JOIN Programmes PG2 ON PG2.ID = MD.DutyProgramId2
		LEFT JOIN Programmes PG3 ON PG3.ID = MD.DutyProgramId3
		LEFT JOIN Programmes PG4 ON PG4.ID = MD.DutyProgramId4
		LEFT JOIN Programmes PG5 ON PG5.ID = MD.DutyProgramId5
		LEFT JOIN Programmes PG6 ON PG6.ID = MD.DutyProgramId6
		LEFT JOIN REF_MasterDutyColours MDC ON MDC.MasterDutyColourID = MD.DutyColourID
		WHERE MD.MasterDutyID = @dutyid AND MD.IsActive = 1

		SET @TmpHistory = ''

		IF @prevLabelId != @LabelId
		BEGIN
			IF(LEN(@TmpHistory) > 10)
				SET @TmpHistory = @TmpHistory + '<BR/>'
			DECLARE @newLabelName VARCHAR(200)
			IF(@prevLabelId = 0)
				SET @prevLabelName = ' '
			IF(@LabelId = 0)
				SET @newLabelName = ' '
			ELSE
			 select @newLabelName = Programme from Programmes where ID = @LabelId
			 SET @TmpHistory = @TmpHistory + 'Duty Label Changed by ' + @currentuser 
			                   + ' on ' + @strUpdateDateTime + ' From ' + @prevLabelName 
							   + ' To ' + @newLabelName
		END

		IF @prevLabelId2 != @LabelId2
		BEGIN
			IF(LEN(@TmpHistory) > 10)
				SET @TmpHistory = @TmpHistory + '<BR/>'

				IF(@prevLabelId2 = 0)
					SET @prevLabelName2 = ' '

				IF(@LabelId2 = 0)
					SET @newLabelName = ' '
			    ELSE
			       select @newLabelName = Programme 
				     from Programmes 
					where ID = @LabelId2

			 SET @TmpHistory = @TmpHistory + 'Duty Label Changed by ' + @currentuser 
			                   + ' on ' + @strUpdateDateTime + ' From ' + @prevLabelName2 
							   + ' To ' + @newLabelName
		END

		IF @prevLabelId3 != @LabelId3
		BEGIN
			IF(LEN(@TmpHistory) > 10)
				SET @TmpHistory = @TmpHistory + '<BR/>'

				IF(@prevLabelId3 = 0)
					SET @prevLabelName3 = ' '

				IF(@LabelId3 = 0)
					SET @newLabelName = ' '
			    ELSE
			       select @newLabelName = Programme 
				     from Programmes 
					where ID = @LabelId3

			 SET @TmpHistory = @TmpHistory + 'Duty Label Changed by ' + @currentuser 
			                   + ' on ' + @strUpdateDateTime + ' From ' + @prevLabelName3 
							   + ' To ' + @newLabelName
		END

		IF @prevLabelId4 != @LabelId4
		BEGIN
			IF(LEN(@TmpHistory) > 10)
				SET @TmpHistory = @TmpHistory + '<BR/>'

				IF(@prevLabelId4 = 0)
					SET @prevLabelName4 = ' '

				IF(@LabelId4 = 0)
					SET @newLabelName = ' '
			    ELSE
			       select @newLabelName = Programme 
				     from Programmes 
					where ID = @LabelId4

			 SET @TmpHistory = @TmpHistory + 'Duty Label Changed by ' + @currentuser 
			                   + ' on ' + @strUpdateDateTime + ' From ' + @prevLabelName4 
							   + ' To ' + @newLabelName
		END

		IF @prevLabelId5 != @LabelId5
		BEGIN
			IF(LEN(@TmpHistory) > 10)
				SET @TmpHistory = @TmpHistory + '<BR/>'

				IF(@prevLabelId5 = 0)
					SET @prevLabelName5 = ' '

				IF(@LabelId5 = 0)
					SET @newLabelName = ' '
			    ELSE
			       select @newLabelName = Programme 
				     from Programmes 
					where ID = @LabelId5

			 SET @TmpHistory = @TmpHistory + 'Duty Label Changed by ' + @currentuser 
			                   + ' on ' + @strUpdateDateTime + ' From ' + @prevLabelName5 
							   + ' To ' + @newLabelName
		END

		IF @prevLabelId6 != @LabelId6
		BEGIN
			IF(LEN(@TmpHistory) > 10)
				SET @TmpHistory = @TmpHistory + '<BR/>'

				IF(@prevLabelId6 = 0)
					SET @prevLabelName6= ' '

				IF(@LabelId6 = 0)
					SET @newLabelName = ' '
			    ELSE
			       select @newLabelName = Programme 
				     from Programmes 
					where ID = @LabelId6

			 SET @TmpHistory = @TmpHistory + 'Duty Label Changed by ' + @currentuser 
			                   + ' on ' + @strUpdateDateTime + ' From ' + @prevLabelName6 
							   + ' To ' + @newLabelName
		END

		IF(LEN(@TmpHistory) < 5)
		 SET @TmpHistory = 'Updated by ' + @currentuser + ' on ' + @strUpdateDateTime

		If ISNULL(@history,'') <> ''
			SET @TmpHistory = @TmpHistory + @history

		IF @RowNum = 0
			SET @status = 2
		ELSE
		BEGIN
			UPDATE MasterDuties
				SET Duration = @duration,
					DutyName = @dutyname,
					DutyTypeID = @dutytypeid,
					WindowDuration = @duration,
					DutyColourID = @dutycolourid,
					LastModDate = @UpdateDateTime,
					LastModBy = @currentuserid,
					BreakTime = @BreakTime,
					IsNightShift = @IsNightShift,
					DutyProgramId1 = @LabelId,
					DutyProgramId2 = @LabelId2,
					DutyProgramId3 = @LabelId3,
					DutyProgramId4 = @LabelId4,
					DutyProgramId5 = @LabelId5,
					DutyProgramId6 = @LabelId6,
					History =  ISNULL(History,'') + CHAR(13) + CHAR(10) + @TmpHistory
				WHERE MasterDutyID = @dutyid and IsActive = 1;

				--Select duty team
			/*	SELECT @oldteamid = TeamID FROM MasterDutyTeams WHERE (DutyID = @dutyid)
				--Update Master Duty Teams
				IF @oldteamid != @teamid
				BEGIN
					UPDATE MasterDutyTeams
						SET
							TeamID = @teamid,
							LastModBy = @currentuserid,
							LastModDate = @UpdateDateTime
						WHERE DutyID = @dutyid
				END


			--update label
			IF EXISTS(SELECt 1 FROM LINK_MasterDuties_Lables WHERE MasterDutyId=@dutyid and IsActive = 1)
			BEGIN
				UPDATE LINK_MasterDuties_Lables 
				SET LabelId = @LabelId, IsActive = 1 
				WHERE MasterDutyId=@dutyid and IsActive=1
			END
			ELSE
			BEGIN
				INSERT INTO LINK_MasterDuties_Lables(MasterDutyId, LabelId, IsActive) 
				VALUES (@dutyid, @LabelId, 1)
			END */

		END
		select 1 intStatus,'Miscellaneous duty record updated successfully.' strStatus, @dutyid NewMiscDutyId;
		RETURN;
	END
END
