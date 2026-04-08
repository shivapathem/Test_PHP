USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_CreateDuty]    Script Date: 26/12/2025 21:58:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER             PROCEDURE  [dbo].[usp_CreateDuty]
@AllocationsID			INT,
@AllocationsSPID		INT = 0,
@SchedulingpersonID		INT,
@DutyName				NVARCHAR(100),
@DutyDate				DATE,
@StartTime				INT,
@EndTime				INT,
@BreakTime				INT,
@Duration				INT,
@DutyColour				INT,
@DutyProgramID1			INT,
@DutyProgramID2			INT,
@DutyProgramID3			INT,
@DutyProgramID4			INT,
@DutyProgramID5			INT,
@DutyProgramID6			INT,
@IsShiftleader			INT = NULL,
@IsNeedCovering			INT,
@IsOverrideOver12		INT,
@DutyComments			VARCHAR(MAX),
@pNetLogin				VARCHAR(30),
@pDutyType				INT = NULL,
@MarkForAttention		INT = NULL,
@IsRequest				INT = NULL,
@MasterDutyId			INT = NULL,
@IsCreateHistory		BIT = 1,
@AllocationsDutyID		INT OUTPUT

AS
BEGIN

    SET NOCOUNT ON

    SET DATEFORMAT YMD

	DECLARE   @vname				VARCHAR(100),
			  @vuserID				INT,
			  @vDutyStartLimeLocal	DATETIME,
			  @vDutyEndTimeLocal	DATETIME,
			  @vErrorMsg		  VARCHAR(1000),
			  @vSPStatus		  INT,
			  @vHistory           NVARCHAR(MAX)='',
			  @updateflag			BIT = 0,
			  @vnewcolourname     NVARCHAR(50),
			  @vnewlabel          NVARCHAR(50),
			  @IsFreeLancer			BIT  = 0;

	DECLARE @EditAllocationStatus TABLE (SPStatus INT,ErrorMsg VARCHAR(1000));

	BEGIN TRY

	   SELECT @vname =  UD_DisplayName,
			  @vuserID = UD_UserID
	     from UserDetails
		WHERE UD_NetLogin = @pNetLogin

	   SET @IsFreeLancer = dbo.ufn_IsFreeLancer(@SchedulingpersonID,@DutyDate)

	  IF ( ISNULL(@pDutyType,0) IN (9,12) )
	    BEGIN

			 INSERT INTO AllocationsDuties
					(
					AD_AllocationsID,
					AD_DutyName,
					AD_iDay,
					AD_DutyDate,
					AD_DutyStartTimeUTC,
					AD_DutyEndTimeUTC,
					AD_DutyStartTimeLocal,
					AD_DutyEndTimeLocal,
					AD_DutyType,
					AD_DutyStatus,
					AD_isAttention,
					AD_isRequest,
					AD_CreatedBy,
					AD_CreatedDate
					 )
			  SELECT @AllocationsID AS allocationid,
					 @DutyName,
					 ixDayInWeek,
					 dDateTime,
					 dDateTime,
					 dDateTime,
					 dDateTime,
					 dDateTime,
					 @pDutyType,
					 1,
					 @MarkForAttention,
					 @IsRequest,
					 @vuserID,
					 getutcdate()
				FROM TimeDimension
			   where dDateTime = @DutyDate

			SET @AllocationsDutyID = @@IDENTITY

			-- Create History completed.

		   IF ( ISNULL(@SchedulingpersonID,0) > 0  AND ISNULL(@AllocationsSPID,0) > 0 )
		    BEGIN

				UPDATE AllocationsScheduledPersons
				   SET ASP_AllocationsDutyID = @AllocationsDutyID,					   
					   ASP_UpdatedBy = @vuserID,
					   ASP_UpdatedDate = getutcdate()
				 WHERE ASP_AllocationsSPID = @AllocationsSPID

			END

		  RETURN 0


		END

	   IF ( @StartTime = 0  AND @EndTime > 0 )
		  SET @vDutyStartLimeLocal = @DutyDate

	   IF ( @StartTime > 0  AND @EndTime = 0 )
		  SET @vDutyEndTimeLocal = DATEADD(DAY,1,@DutyDate)

	   IF ( ISNULL(@StartTime,0) > 0 )
	    BEGIN
		 SET @vDutyStartLimeLocal = CASE
									 WHEN @StartTime IS NULL THEN NULL
									 WHEN @StartTime = 0 AND @EndTime = 0 THEN NULL
									 WHEN @StartTime = 0 AND @EndTime > 0 THEN @DutyDate
									 WHEN @StartTime > 0 THEN dbo.ufn_ConvertToDateTime(@DutyDate,@StartTime)
									 ELSE dbo.ufn_ConvertToDateTime(@DutyDate,@StartTime) END
		END

	   IF (ISNULL(@EndTime,0) > 0 )
	    BEGIN

		 SET @vDutyEndTimeLocal = CASE
									 WHEN @EndTime IS  NULL THEN NULL
									 WHEN @StartTime = 0 AND @EndTime = 0 THEN NULL
									 WHEN @StartTime > 0 AND @EndTime = 0 THEN DATEADD(DAY,1,@DutyDate)
									 WHEN @EndTime = 86400 THEN DATEADD(DAY,1,@DutyDate)
									 WHEN @EndTime > 86400 THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,@DutyDate),@EndTime-86400)
									 WHEN @EndTime < @StartTime THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,@DutyDate),@EndTime)
									 ELSE dbo.ufn_ConvertToDateTime(@DutyDate,@EndTime) END

		END

		INSERT INTO AllocationsDuties
						(
						AD_AllocationsID,
						AD_DutyName,
						AD_Duration,
						AD_PlannedDuration,
						AD_iDay,
						AD_StartTimeSec,
						AD_EndTimeSec,
						AD_Comments,
						AD_DutyDate,
						AD_DutyStartTimeUTC,
						AD_DutyEndTimeUTC,
						AD_DutyStartTimeLocal,
						AD_DutyEndTimeLocal,
						AD_DutyBreakTime,
						AD_MasterDutyID,
						AD_DutyType,
						AD_DutyStatus,
						AD_DutyColourID,
						AD_isAttention,
						AD_isRequest,
						AD_PlannedDutyBreakTime,
						AD_IsNeedCovering,
						AD_IsOverrideOver12,
						AD_IsDutyEdited,
						AD_DutyProgramID1,
						AD_DutyProgramID2,
						AD_DutyProgramID3,
						AD_DutyProgramID4,
						AD_DutyProgramID5,
						AD_DutyProgramID6,
						AD_IsEditedDutyAttention,
						AD_CreatedBy,
						AD_CreatedDate,
						AD_UpdatedBy,
						AD_UpdatedDate
						 )
			SELECT		@AllocationsID,
						@DutyName,
						@Duration,
						0,
						td.ixDayInWeek,
						@StartTime,
						@EndTime,
						@DutyComments,
						@DutyDate,
						@vDutyStartLimeLocal,
						@vDutyEndTimeLocal,
						@vDutyStartLimeLocal,
						@vDutyEndTimeLocal,
						@BreakTime,
						@MasterDutyId,
						CASE WHEN (ISNULL(@StartTime,0) > 0 OR ISNULL(@Endtime,0) > 0 )
							 THEN 1
							 ELSE 2 END as DutyType,
						CASE WHEN ISNULL(@SchedulingpersonID,0) = 0
							 THEN 0 ELSE 1
							 END DutyStatus,
						@DutyColour,
						0,
						0,
						0,
						@IsNeedCovering,
						@IsOverrideOver12,
						1,
						@DutyProgramID1,
						@DutyProgramID2,
						@DutyProgramID3,
						@DutyProgramID4,
						@DutyProgramID5,
						@DutyProgramID6,
						CASE WHEN @IsShiftleader = 1 THEN 1 ELSE 0 END,
						al.AL_CreatedBy,
						al.AL_CreatedDate,
						@vuserID,
						GETUTCDATE()
			 FROM Allocations AL
			INNER JOIN TimeDimension TD on TD.ixYearWeek = AL.AL_WeekNumber
			where al.AL_AllocationsID = @AllocationsID
			  and td.dDateTime = @DutyDate

			SET @AllocationsDutyID = @@Identity

			-- Create History

		IF ( @IsCreateHistory = 1 )
		 BEGIN

			 SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
			 SET @vHistory = @vHistory+'Duty Name changed by '+@vname+' On ' + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
							 +' From U To '+@DutyName+'.'
			 SET @updateflag = 1

		   /*IF ISNULL(@duration,0) > 0
		    BEGIN

			 SET @vHistory = @vHistory+'Duration amended by '+@vname+' On ' + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
				+' From 0 To '
				+ right('0'+CAST( isnull(@duration,0) / 3600 AS varchar(2)),2) + ':'
				+ right('0' + CAST( (isnull(@duration,0) % 3600)/60 AS varchar(2)),2)+'.'

			END

			IF ( ISNULL(@StartTime,0) > 0 )
			 BEGIN

			 SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
			 SET @vHistory = @vHistory+'Start Time amended by '+@vname+' On ' + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
							 +' From 00:00 To '
							 + right('0'+CAST( isnull(@StartTime,0) / 3600 AS varchar(2)),2) + ':'
							 + right('0' + CAST((isnull(@StartTime,0) % 3600)/60 AS varchar(2)),2)+'.'

			 END

			IF ( ISNULL(@EndTime,0) > 0 )
			 BEGIN

			 SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
			 SET @vHistory = @vHistory+'End Time amended by '+@vname+' On ' + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
							 +' From 00:00 To '
							 + right('0'+ CAST( isnull(@EndTime,0) / 3600 AS varchar(2)),2) + ':'
							 + right('0' + CAST((isnull(@EndTime,0) % 3600 )/60 AS varchar(2)),2)+'.'

			 END */

			IF ( ISNULL(@BreakTime,0) > 0 )
			 BEGIN

			 SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
			 SET @vHistory = @vHistory+'Break Time amended by '+@vname+' On ' + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
							 +' From 0 To '
							 + right('0'+CAST( isnull(@BreakTime,0) / 3600 AS varchar(2)),2) + ':'
							 + right('0' + CAST((isnull(@BreakTime,0) % 3600)/60 AS varchar(2)),2)+'.'

			 END

			IF ( ISNULL(@DutyColour,0) > 0 )
			 BEGIN

			 SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
			 SELECT @vnewcolourname = ColourBackground FROM REF_MasterDutyColours (Nolock) WHERE MasterDutyColourID = @DutyColour
			 SET @vHistory = @vHistory+'Duty Colour Changed by '+@vname+' On '
									  + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
									  +' From <span style=''background-color:#'
									  +'''>&nbsp;&nbsp;&nbsp;&nbsp;</span> To <span style=''background-color:#'
									  +CAST(ISNULL(@vnewcolourname,'') as nvarchar)+'''>&nbsp;&nbsp;&nbsp;&nbsp;</span> '

			 END

			/*IF ( ISNULL(@IsNeedCovering,0) > 0 )
			 BEGIN

			 SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
			 SET @vHistory = @vHistory+'Need coveringflag changed by '+@vname+' On ' + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
							 +' From  To '
							 +case when @IsNeedCovering = 1 then 'Yes' else 'No' end +'.'

			 END */

			/*IF ( ISNULL(@IsOverrideOver12,0) > 0 )
			 BEGIN

			 SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
			 SET @vHistory = @vHistory+'Override over 12 flag changed by '+@vname+' On '
							 + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
							 +' From  To '
							 +case when @IsOverrideOver12 = 1 then 'Yes' else 'No' end +'.'

			 END */

			IF ( ISNULL(@DutyProgramID1,0) > 0 )
			 BEGIN

			 SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
			 SELECT @vnewlabel = Programme FROM Programmes (Nolock) WHERE ID = @DutyProgramID1
			 SET @vHistory = @vHistory+'Duty Label Changed by '+@vname+' On ' + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
							 +' From  To '+ISNULL(@vnewlabel,'')+'.'

			 END


			IF ( ISNULL(@DutyProgramID2,0) > 0 )
			 BEGIN

			 SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
			 SELECT @vnewlabel = Programme FROM Programmes (Nolock) WHERE ID = @DutyProgramID2
			 SET @vHistory = @vHistory+'Duty Label Changed by '+@vname+' On ' + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
							 +' From  To '+ISNULL(@vnewlabel,'')+'.'

			 END

			IF ( ISNULL(@DutyProgramID3,0) > 0 )
			 BEGIN

			 SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
			 SELECT @vnewlabel = Programme FROM Programmes (Nolock) WHERE ID = @DutyProgramID3
			 SET @vHistory = @vHistory+'Duty Label Changed by '+@vname+' On ' + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
							 +' From  To '+ISNULL(@vnewlabel,'')+'.'

			 END

			IF ( ISNULL(@DutyProgramID4,0) > 0 )
			 BEGIN

			 SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
			 SELECT @vnewlabel = Programme FROM Programmes (Nolock) WHERE ID = @DutyProgramID4
			 SET @vHistory = @vHistory+'Duty Label Changed by '+@vname+' On ' + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
							 +' From  To '+ISNULL(@vnewlabel,'')+'.'

			 END

			IF ( ISNULL(@DutyProgramID5,0) > 0 )
			 BEGIN

			 SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
			 SELECT @vnewlabel = Programme FROM Programmes (Nolock) WHERE ID = @DutyProgramID5
			 SET @vHistory = @vHistory+'Duty Label Changed by '+@vname+' On ' + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
							 +' From  To '+ISNULL(@vnewlabel,'')+'.'

			 END

			IF ( ISNULL(@DutyProgramID6,0) > 0 )
			 BEGIN

			 SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
			 SELECT @vnewlabel = Programme FROM Programmes (Nolock) WHERE ID = @DutyProgramID6
			 SET @vHistory = @vHistory+'Duty Label Changed by '+@vname+' On ' + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
							 +' From  To '+ISNULL(@vnewlabel,'')+'.'

			 END

			INSERT INTO history ( historytype,
								  attributeid,
								  HistorySubType,
								  datetime,
								  userid,
								  history )
			SELECT ht.id AS historytype,
				   @AllocationsDutyID AS attributeid,
				   'DH' AS HistorySubType,
				   getdate(),
				   @vuserID,
				   @vHistory
			  FROM HistoryTypes HT (Nolock)
			 WHERE historytype = 'AllocationDuty'

		 END


			-- Create History completed.


		   IF ( ISNULL(@SchedulingpersonID,0) > 0  AND ISNULL(@AllocationsSPID,0) > 0 )
		    BEGIN

				UPDATE AllocationsScheduledPersons
				   SET ASP_AllocationsDutyID = @AllocationsDutyID,
					   ASP_WIADStatus =  CASE WHEN @IsFreeLancer = 1 THEN 2 ELSE ASP_WIADStatus END,					   
					   ASP_UpdatedBy = @vuserID,
					   ASP_UpdatedDate = GETUTCDATE()
				 WHERE ASP_AllocationsSPID = @AllocationsSPID

			END

	 RETURN 0

	END TRY

	BEGIN CATCH

		INSERT INTO ErrorLog
			(ErrorNumber,
				ErrorState,
				ErrorSeverity,
				ErrorProcedure,
				ErrorLine,
				ErrorMessage,
				ErrorDateTime,
				UserName
			)
		SELECT ERROR_NUMBER() AS ErrorNumber,
			ERROR_STATE() AS ErrorState,
			ERROR_SEVERITY() AS ErrorSeverity,
			ERROR_PROCEDURE() AS ErrorProcedure,
			ERROR_LINE() AS ErrorLine,
			ERROR_MESSAGE() AS ErrorMessage,
			getutcdate(),
			@vuserID

	 	 RETURN 1

	END CATCH;
END