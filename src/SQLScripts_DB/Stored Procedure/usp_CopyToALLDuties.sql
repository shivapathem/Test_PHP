USE [BBCSchedules_WP]
GO
/****** Object:  StoredProcedure [dbo].[usp_CopyToALLDuties]    Script Date: 30/03/2026 14:11:20 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER           PROCEDURE [dbo].[usp_CopyToALLDuties]
@pweekNumber		 INT,
@pDutyID			 INT,
@pTeamID             INT,
@pNetLogin           VARCHAR(30)

AS
BEGIN

   SET NOCOUNT ON;
   SET DATEFORMAT YMD;

   DECLARE 	 @vMasterDuty      INT,
			 @viday            INT,
			 @createdutycnt    INT,
			 @removedutycnt    INT,
			 @vWeekNumber      INT,
			 @vAllocationID    INT,
			 @AllocationsDutyID	INT,
			 @AllocationsSPID	INT,
			 @vname            VARCHAR(50),
			 @vuserID          INT,
			 @vTeamID          INT,
			 @vSQL             VARCHAR(MAX),
			 @vHistory         NVARCHAR(MAX),
			 @vstartweek       INT,
			 @vendweek         INT,
			 @vdutyname        NVARCHAR(60),
			 @vdutycolourid    INT,
			 @vduration        INT,
			 @vstarttime       INT,
			 @vendtime         INT,
			 @vbackcolour      NCHAR(10),
			 @vforecolour      NCHAR(10),
			 @vbreaktime       INT,
			 @aldutyname       NVARCHAR(60),
			 @alstarttime      INT,
			 @alendtime        INT,
			 @albackcolour     NCHAR(10),
			 @alfontcolour     NCHAR(10),
			 @alstartdate      DATETIME,
			 @alenddate        DATETIME,
			 @aldutybreaktime  INT,
			 @aldutycolorid    INT,
			 @alduration       INT,
			 @alid             INT,
			 @aldutydate       DATETIME,
			 @dutyProgramId    INT,
             @dutyProgramId2   INT,
			 @dutyProgramId3   INT,
			 @dutyProgramId4   INT,
			 @dutyProgramId5   INT,
			 @dutyProgramId6   INT,
			 @IsNeedCovering   INT,
			 @IsOverrideOver12 INT,
			 @isDutyEditedPostWeek   BIT,
			 @MarkedOvertime         BIT,
			 @isRequest              BIT,
			 @ChargingStatus         BIT,
			 @SkipFlag               BIT,
			 @isAttention			 INT,
			 @LeaveStartTime		 INT,
			 @LeaveEndTime			 INT,
			 @HistoryTypeJob         INT,
			 @HistoryTypeDuty        INT,
			 @vDurationFlag          BIT = 0,
			 @IsOverrideOver12UpdateFlag BIT = 0,
			 @MarkOverTwelve         INT,
			 @DutyType				 INT,
			 @HistoryTypePerson		 INT;


    DECLARE  @vdutyprogramid    INT,
             @vdutyprogramid2   INT,
			 @vdutyprogramid3   INT,
			 @vdutyprogramid4   INT,
			 @vdutyprogramid5   INT,
			 @vdutyprogramid6   INT,
			 @vIsNeedCovering   INT,
			 @vIsOverrideOver12 INT ;

    DECLARE  @updateflag        INT = 0,
			 @vTimeChangeFlag   INT = 0,
			 @voldcolourname    NVARCHAR(50),
			 @vnewcolourname    NVARCHAR(50),
			 @Message           VARCHAR(100),
			 @NewLabel          NVARCHAR(50),
			 @OldLabel          NVARCHAR(50),
			 @ReturnValue		INT,
			 @StartDate			DATE,
			 @EndDate			DATE,
			 @SchedulingPersonID	INT;

										   
   DECLARE  @TempHistory	TABLE (HistoryID INT,
							       AttributeID INT,
								   HistorySubType VARCHAR(10),
								   HistoryType INT,
								   UserID INT,
								   History NVARCHAR(MAX),
								   CreateDateTime datetime)

   DECLARE @TempJobList TABLE ( JobID INT)

   DECLARE @TempAllocationList TABLE (ID INT,
									  ActionType VARCHAR(1))

      SELECT  @vname =  UD_DisplayName,
			  @vuserID = UD_UserID
	    FROM UserDetails sd (nolock)
	   WHERE sd.UD_NetLogin=@pNetLogin

	SELECT @HistoryTypeJob = id
	  FROM historytypes
	 WHERE historytype='AllocationJobs'	 

	SELECT @HistoryTypeDuty = id
	  FROM historytypes
	 WHERE historytype='AllocationDuty'

	SELECT @HistoryTypePerson = id
	  FROM historytypes
	 WHERE historytype='AllocationScheduledPerson'

 BEGIN TRY
  BEGIN TRANSACTION

	IF ( @pweekNumber < 189999 )
     BEGIN
	   THROW 51000, 'Week Number Entered is Incorrect. Please Enter Valid Week Number.', 1;
     END;

	IF NOT EXISTS ( SELECT ixYearWeek
					  FROM TimeDimension  (NOLOCK)
					 WHERE CAST(dDateTime as DATE) = cast(getdate() as date)
					   AND @pweekNumber >= ixYearWeek 
					)
	 BEGIN
		THROW 51000, 'Cannot create allocation for past weeks ', 1;
	 END;

	 SELECT 12345678009123 AS ID , 'A' as ActionType INTO #TempAllocationList

     --  Master Duty creation and Updation Started

	 BEGIN

		SELECT @vdutyname = MD.dutyname,
			   @vdutycolourid = MD.dutycolourid,
			   @vduration =  MD.duration ,
			   @vstartweek = MD.startweek,
			   @vendweek = MD.endweek,
			   @vstarttime = case when MD.StartTime >= 86400 then ( MD.StartTime - 86400) else MD.StartTime end,
			   @vendtime = case when MD.EndTime >= 86400 then (MD.EndTime - 86400) else MD.EndTime end,
			   @vbackcolour = MD.backcolour,
			   @vforecolour = md.forecolour,
			   @vbreaktime = MD.breaktime,
			   @vdutyprogramid = MD.DutyProgramId1,
			   @vdutyprogramid2 = MD.DutyProgramId2,
			   @vdutyprogramid3 = MD.DutyProgramId3,
			   @vdutyprogramid4 = MD.DutyProgramId4,
			   @vdutyprogramid5 = MD.DutyProgramId5,
			   @vdutyprogramid6 = MD.DutyProgramId6,
			   @vIsNeedCovering = MD.IsNeedCovering,
			   @vIsOverrideOver12 = MD.IsOverrideOver12,
			   @DutyType = MD.DutyTypeID
		FROM   masterduties MD (NOLOCK)
		WHERE  md.masterdutyid = @pDutyID

        IF ( @pweekNumber < @vstartweek or @pweekNumber >  @vendweek )
			BEGIN
			  THROW 51000, 'The week you have specified is outside the date range of this master duty.', 1;
			END;

		IF NOT EXISTS ( SELECT TOP 1 AL_WeekNumber
				          FROM Allocations  (NOLOCK)
				         WHERE AL_WeekNumber >= @pweekNumber 
						   and AL_SchedulingTeamID = @pTeamID 
						   and AL_Status <> 9
					   )
		 BEGIN
		   THROW 51001, 'There are no ''Created'' Weeks To Add This Duty To.', 1;
		 END
     END

     --	Correct the Allocation details as per Forward Planning Start
	 
	 BEGIN

		SELECT AD_DutyName			AS DutyName,
			   AD_StartTimeSec		AS StartTime,
			   AD_EndTimeSec		AS EndTime,
			   AD_DutyBreakTime		AS dutybreaktime,
			   AD_DutyColourID		AS dutycolorid,
			   AD_Duration			AS duration,
			   AL_AllocationsID		AS AllocationsID,
			   AD_AllocationsDutyID	AS AllocationsDutyID,
			   ASP_AllocationsSPID	AS AllocationsSPID,
			   AD_DutyDate			AS dutydate,
			   AD_DutyProgramID1	AS dutyProgramId,
			   AD_DutyProgramID2	AS DutyProgramId2,
			   AD_DutyProgramID3	AS DutyProgramId3,
			   AD_DutyProgramID4	AS DutyProgramId4,
			   AD_DutyProgramID5	AS DutyProgramId5,
			   AD_DutyProgramID6	AS DutyProgramId6,
			   AD_IsNeedCovering	AS IsNeedCovering,
			   AD_IsOverrideOver12	AS IsOverrideOver12,
			   ISNULL(AD_IsDutyEdited,0)	AS isDutyEditedPostWeek,
			   ISNULL(ASP_MarkedOverTime,0)	AS MarkedOvertime,
			   ISNULL(AD_isAttention,0)		AS isAttention,
			   ISNULL(AD_isRequest,0)		AS isRequest,
			   ASP_LeaveStartTimeSec	AS LeaveStartTime,
			   ASP_LeaveStartTimeLocal	AS LeaveStartTimeLocal,
			   ASP_LeaveEndTimeSec		AS LeaveEndTime,
			   ASP_LeaveEndTimeLocal	AS LeaveEndTimeLocal,
			   ASP_OverTwelveStatus		AS MarkOverTwelve,
			   ISNULL(ASP_ChargingStatus,0)		AS ChargingStatus,
			   'X' as RecordAction,
			   ASP_OverTwelveHrs		AS OverTwelveHrs,
			   ASP_IsOverseasOverTwelve	AS IsOverseasOverTwelve,
			   CASE WHEN ASP_WIADStatus = 2 THEN 1 ELSE 0 END markactual,
			   CASE WHEN DATEDIFF(DAY,AD_DutyStartTimeLocal,AD_DutyEndTimeLocal) = 1 THEN 1 ELSE 0 END  aftermidnight,
			   AD_DutyStartTimeLocal	AS StartDate,
			   AD_DutyEndTimeLocal		AS EndDate,
			   AL_SchedulingTeamID		AS SchedulingTeamId,
			   ASP_SchedulingPersonID	AS SchedulingPersonID,
			   AL_WeekNumber			AS WeekNumber,
			   AD_iDay					AS iDay,
			   AD_MasterDutyID			AS MasterDutyId,
			   0 as SkipDueToPDL,
			   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL_SchedulingTeamID THEN 0 ELSE 1 END AS IsHomeTeam,
			   CASE WHEN ASP_WIADStatus = 1 THEN 1 ELSE 0 END MarkWiad,
			   CASE WHEN @vstarttime IS NULL THEN NULL
				 WHEN @vstarttime = 0 AND @vEndTime = 0 THEN NULL
				 WHEN @vstarttime = 0 AND @vEndTime > 0 THEN AD_DutyDate
				 WHEN @vstarttime > 0 THEN dbo.ufn_ConvertToDateTime(AD_DutyDate,@vstarttime)
				 ELSE dbo.ufn_ConvertToDateTime(AD_DutyDate,@vstarttime) END AS NewStartDateLocal,
               CASE WHEN @vEndTime IS  NULL THEN NULL
				 WHEN @vstarttime = 0 AND @vEndTime = 0 THEN NULL
				 WHEN @vstarttime > 0 AND @vEndTime = 0 THEN DATEADD(DAY,1,AD_DutyDate)
				 WHEN @vEndTime = 86400 THEN DATEADD(DAY,1,AD_DutyDate)
				 WHEN @vEndTime > 86400 THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,AD_DutyDate),@vEndTime-86400)
				 WHEN @vEndTime < @vstarttime THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,AD_DutyDate),@vEndTime)
				 ELSE dbo.ufn_ConvertToDateTime(AD_DutyDate,@vEndTime) END AS NewEndDateLocal
			   INTO #TempAllocations
		  FROM Allocations Al
		 INNER JOIN AllocationsDuties AD ON AL_AllocationsID = AD_AllocationsID
		  LEFT JOIN AllocationsScheduledPersons ASP ON ASP_AllocationsDutyID = AD_AllocationsDutyID
		 WHERE AL_WeekNumber >= @pweeknumber
		   AND AL_SchedulingTeamID = @pteamId
		   AND AD_MasterDutyID = @pDutyID

		UPDATE TL
		   SET TL.SkipDueToPDL = 1
		  FROM #TempAllocations TL   
		 WHERE ( ISNULL(LeaveStartTime,0) > 0 OR ISNULL(LeaveEndTime,0) > 0 )
		   AND ( LeaveStartTimeLocal < NewStartDateLocal OR LeaveEndTimeLocal > NewEndDateLocal )
		  
		DECLARE CUR_Upd_Duty CURSOR FOR
		SELECT al.dutyname,
			   al.starttime,
			   al.endtime,
			   al.dutybreaktime,
			   al.dutycolorid,
			   al.duration,
			   al.AllocationsID,
			   al.AllocationsDutyID,
			   al.AllocationsSPID,
			   al.dutydate,
			   al.dutyProgramId,
			   al.DutyProgramId2,
			   al.DutyProgramId3,
			   al.DutyProgramId4,
			   al.DutyProgramId5,
			   al.DutyProgramId6,
			   al.IsNeedCovering,
			   al.IsOverrideOver12,
			   al.isDutyEditedPostWeek,
			   al.MarkedOvertime,
			   al.isRequest,
			   al.ChargingStatus,
			   al.isAttention,
			   al.LeaveStartTime,
			   al.LeaveEndTime,
			   al.MarkOverTwelve
		  FROM #TempAllocations al 
		 WHERE al.isRequest = 0
		   AND al.ChargingStatus = 0
		   AND al.isAttention = 0 
		   AND al.MarkedOvertime = 0
		   AND al.SkipDueToPDL = 0   
		   AND al.isDutyEditedPostWeek = 0

      OPEN CUR_Upd_Duty

	  FETCH NEXT FROM CUR_Upd_Duty INTO
		@aldutyname,
		@alstarttime,
		@alendtime,
		@aldutybreaktime,
		@aldutycolorid,
		@alduration,
		@alid,
		@AllocationsDutyID,
		@AllocationsSPID,
		@aldutydate,
		@dutyProgramId,
		@dutyProgramId2,
		@dutyProgramId3,
		@dutyProgramId4,
		@dutyProgramId5,
		@dutyProgramId6,
		@IsNeedCovering,
		@IsOverrideOver12,
		@isDutyEditedPostWeek,
		@MarkedOvertime,
		@isRequest,
		@ChargingStatus,
		@isAttention,
		@LeaveStartTime,
		@LeaveEndTime,
		@MarkOverTwelve



      WHILE @@FETCH_STATUS = 0
		BEGIN

			SET @vSQL = 'Update #TempAllocations SET '
			SET @vHistory = ''

			IF ( @aldutyname <> @vdutyname )
			 BEGIN
				SET @updateflag = 1
				SET @vSQL = @vSQL+' dutyname = '''+@vdutyname+''', '
				SET @vHistory = @vHistory+'Duty Name changed by '+@vname+' On '
  						        + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
								+' From '+@aldutyname+' To '+@vdutyname+'.'
			 END

			IF ( ISNULL(@vIsNeedCovering,0) <> ISNULL(@IsNeedCovering,0) )
			 BEGIN
				SET @vSQL = @vSQL+' IsNeedCovering = '+cast(@vIsNeedCovering as varchar)+', '
				SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END

				SET @vHistory = @vHistory+'Need Covering flag Changed by '+@vname+' On '
																+ FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
																+' From '
																+ case when @IsNeedCovering = 1 then 'Yes' else 'No' end
																+' To '
																+ case when @vIsNeedCovering = 1 then 'Yes' else 'No' end
																+'.'
			    SET @updateflag = 1
			 END


		    IF ( ISNULL(@vIsOverrideOver12,0) <> ISNULL(@IsOverrideOver12,0) )
			 BEGIN
				SET @vSQL = @vSQL+' IsOverrideOver12 = '+cast(@vIsOverrideOver12 as varchar)+', '
				SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
				SET @IsOverrideOver12UpdateFlag = 1

				SET @vHistory = @vHistory+'Disable override flag Changed by '+@vname+' On '
																+ FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
																+' From '
																+ case when @IsOverrideOver12 = 1 then 'Yes' else 'No' end
																+' To '
																+ case when @vIsOverrideOver12 = 1 then 'Yes' else 'No' end
																+'.'
				SET @updateflag = 1
			 END

			IF ( ISNULL(@vdutyprogramid,0) <> ISNULL(@dutyProgramId,0) )
			 BEGIN
				SET @vSQL = @vSQL+' dutyProgramId = '+cast(@vdutyprogramid as varchar)+', '
				SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
				SELECT @OldLabel = Programme FROM Programmes (NOLOCK) WHERE ID = @dutyProgramId
				SELECT @NewLabel = Programme FROM Programmes (NOLOCK) WHERE ID = @vdutyprogramid
					SET @vHistory = @vHistory+'Duty Label Changed by '+@vname+' On '
																  + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
																  +' From '+ ISNULL(@OldLabel,'')
																  +' To '+ ISNULL(@NewLabel,'') +'.'
				SET @updateflag = 1
			 END

			IF ( ISNULL(@vdutyprogramid2,0) <> ISNULL(@dutyProgramId2,0) )
			 BEGIN
				SET @vSQL = @vSQL+' dutyProgramId2 = '+cast(@vdutyprogramid2 as varchar)+', '
				SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
				SELECT @OldLabel = Programme FROM Programmes (NOLOCK) WHERE ID = @dutyProgramId2
				SELECT @NewLabel = Programme FROM Programmes (NOLOCK) WHERE ID = @vdutyprogramid2
					SET @vHistory = @vHistory+'Duty Label Changed by '+@vname+' On '
																  + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
																  +' From '+ ISNULL(@OldLabel,'')
																  +' To '+ ISNULL(@NewLabel,'') +'.'
				SET @updateflag = 1
			 END

			IF ( ISNULL(@vdutyprogramid3,0) <> ISNULL(@dutyProgramId3,0) )
			 BEGIN
				SET @vSQL = @vSQL+' dutyProgramId3 = '+cast(@vdutyprogramid3 as varchar)+', '
				SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
				SELECT @OldLabel = Programme FROM Programmes (NOLOCK) WHERE ID = @dutyProgramId3
				SELECT @NewLabel = Programme FROM Programmes (NOLOCK) WHERE ID = @vdutyprogramid3
				SET @vHistory = @vHistory+'Duty Label Changed by '+@vname+' On '
																  + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
																  +' From '+ ISNULL(@OldLabel,'')
																  +' To '+ ISNULL(@NewLabel,'') +'.'
				SET @updateflag = 1
			 END

			IF ( ISNULL(@vdutyprogramid4,0) <> ISNULL(@dutyProgramId4,0) )
			 BEGIN 
				SET @vSQL = @vSQL+' dutyProgramId4 = '+cast(@vdutyprogramid4 as varchar)+', '
				SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
				SELECT @OldLabel = Programme FROM Programmes (NOLOCK) WHERE ID = @dutyProgramId4
				SELECT @NewLabel = Programme FROM Programmes (NOLOCK) WHERE ID = @vdutyprogramid4
				SET @vHistory = @vHistory+'Duty Label Changed by '+@vname+' On '
																  + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
																  +' From '+ ISNULL(@OldLabel,'')
																  +' To '+ ISNULL(@NewLabel,'') +'.'
				SET @updateflag = 1
			 END

			IF ( ISNULL(@vdutyprogramid5,0) <> ISNULL(@dutyProgramId5,0) )
			 BEGIN
				SET @vSQL = @vSQL+' dutyProgramId5 = '+cast(@vdutyprogramid5 as varchar)+', '
				SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
				SELECT @OldLabel = Programme FROM Programmes (NOLOCK) WHERE ID = @dutyProgramId5
				SELECT @NewLabel = Programme FROM Programmes (NOLOCK) WHERE ID = @vdutyprogramid5
				SET @vHistory = @vHistory+'Duty Label Changed by '+@vname+' On '
																  + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
																  +' From '+ ISNULL(@OldLabel,'')
																  +' To '+ ISNULL(@NewLabel,'') +'.'
				SET @updateflag = 1
			 END

			IF ( ISNULL(@vdutyprogramid6,0) <> ISNULL(@dutyProgramId6,0) )
			 BEGIN
				SET @vSQL = @vSQL+' dutyProgramId6 = '+cast(@vdutyprogramid6 as varchar)+', '
				SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
				SELECT @OldLabel = Programme FROM Programmes (NOLOCK) WHERE ID = @dutyProgramId6
				SELECT @NewLabel = Programme FROM Programmes (NOLOCK) WHERE ID = @vdutyprogramid6
				SET @vHistory = @vHistory+'Duty Label Changed by '+@vname+' On '
																  + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
																  +' From '+ ISNULL(@OldLabel,'')
																  +' To '+ ISNULL(@NewLabel,'') +'.'
				SET @updateflag = 1
			 END


			IF ( ISNULL(@aldutycolorid,0) <> ISNULL(@vdutycolourid,0) )
			 BEGIN
				SET @vSQL = @vSQL+' dutyColorId = '+cast(@vdutycolourid as varchar)+', '
				SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
				SELECT @voldcolourname = ColourName FROM REF_MasterDutyColours (NOLOCK) WHERE MasterDutyColourID = @aldutycolorid
				SELECT @vnewcolourname = ColourName FROM REF_MasterDutyColours (NOLOCK) WHERE MasterDutyColourID = @vdutycolourid
				SET @vHistory = @vHistory+'Duty Colour Changed by '+@vname+' On '
						                          + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
										          +' From '+ISNULL(@voldcolourname,'')
												  +' To '+ISNULL(@vnewcolourname,'')+'.'
				SET @updateflag = 1
			 END

			IF ( ISNULL(@vduration,0) <> 0
				AND ISNULL(@alduration,0) <> ISNULL(@vduration,0)
    			AND ISNULL(@vStartTime,0) = 0
				AND ISNULL(@vEndTime,0)=0  )
			 BEGIN

			    SET @vDurationFlag = 1
				SET @vSQL = @vSQL+' duration = '+cast(@vduration as varchar)+', '
				SET @vHistory = @vHistory+'Duration amended by '+@vname+' On '
								+ FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
								+ ' From '+right('0'+CAST( ISNULL(@alduration,0) / 3600 AS varchar(2)),2) + ':'
								+ right('0' + CAST( ( ISNULL(@alduration,0) % 3600 )/60 AS varchar(2)),2)+' To '
								+ right('0'+CAST( ISNULL(@vduration,0) / 3600 AS varchar(2)),2) + ':'
								+ right('0' + CAST( (ISNULL(@vduration,0) % 3600)/60 AS varchar(2)),2)+'.'

				SET @updateflag = 1

			 END

			IF ( ISNULL(@vstarttime,0) <> 0 AND ISNULL(@alstarttime,0) <> ISNULL(@vstarttime,0)  )
			 BEGIN

				SET @vTimeChangeFlag = 1
				SET @vSQL = @vSQL+' StartTime = '+cast(@vstarttime as varchar)+', '
				SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
				SET @vHistory = @vHistory+'Start Time amended by '+@vname+' On '
										+ FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
										+' From '+right('0'+CAST( ISNULL(@alstarttime,0) / 3600 AS varchar(2)),2) + ':'
										+ right('0' + CAST( ( ISNULL(@alstarttime,0) % 3600 )/60 AS varchar(2)),2)+' To '
										+ right('0'+CAST( ISNULL(@vstarttime,0) / 3600 AS varchar(2)),2) + ':'
										+ right('0' + CAST( (ISNULL(@vstarttime,0) % 3600 )/60 AS varchar(2)),2)+'.'

				SET @updateflag = 1
				SET @alstartdate = CAST(Concat(
										Format (@aldutydate, 'yyyy-MM-dd'),
												' ', (
												RIGHT(
												'0' + Cast(Cast(@vstarttime AS INT)
												/ 3600
												AS VARCHAR )
												, 2)
												+
												':'
												+ RIGHT('0' +
												Cast((Cast(@vstarttime AS INT) / 60
												) % 60
												AS VARCHAR), 2)
														+ ':'
														+ RIGHT('0' +
												Cast(Cast(@vstarttime AS INT) % 60
												AS
												VARCHAR ), 2)
															+ '.000' )) as datetime)

				SET @vSQL = @vSQL+' StartDate = '''+cast(format(@alstartdate,'yyyy-MM-dd HH:mm:ss') as varchar)+''', '

			 END

		    IF ( ISNULL(@vendtime,0) <> 0 AND ISNULL(@alendtime,0) <> ISNULL(@vendtime,0)  )
			 BEGIN

				SET @vTimeChangeFlag = 1
				SET @vSQL = @vSQL+' EndTime = '+cast(@vendtime as varchar)+', '
				SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
				SET @vHistory = @vHistory+'End Time amended by '+@vname+' On '
										+ FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
										+' From '+right('0'+CAST( ISNULL(@alendtime,0) / 3600 AS varchar(2)),2) + ':'
										+ right('0' + CAST( ( ISNULL(@alendtime,0) % 3600 ) /60 AS varchar(2)),2)+' To '
										+ right('0'+CAST( ISNULL(@vendtime,0) / 3600 AS varchar(2)),2) + ':'
										+ right('0' + CAST( ( ISNULL(@vendtime,0) % 3600 ) /60 AS varchar(2)),2)+'.'
				SET @updateflag = 1

				SET @alenddate = cast( Concat(Format (CASE WHEN @vendtime < @vstarttime THEN
																DATEADD(DAY,1,@aldutydate)
														ELSE @aldutydate
														END, 'yyyy-MM-dd'), ' '
												, (
												RIGHT('0' + Cast(Cast(@vendtime AS
												INT) /
												3600 AS VARCHAR ), 2)
												+ ':'
												+ RIGHT('0' + Cast((Cast(@vendtime
												AS INT)
												/ 60) % 60 AS VARCHAR), 2)
												+ ':'
												+ RIGHT('0' + Cast(Cast(@vendtime
												AS INT)
												% 60 AS VARCHAR ), 2)
												+ '.000' )) as datetime)

				SET @vSQL = @vSQL+' EndDate = '''+cast(format(@alenddate,'yyyy-MM-dd HH:mm:ss') as varchar)+''', '

			 END


		    IF ( ISNULL(@aldutybreaktime,0) <> ISNULL(@vbreaktime,0)  )
			 BEGIN

				SET @vSQL = @vSQL+' dutyBreakTime = '+cast(@vbreaktime as varchar)+', '
				SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
				SET @vHistory = @vHistory+'Break Time amended by '+@vname+' On '
						                + FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
										+' From '+right('0'+CAST( ISNULL(@aldutybreaktime,0) / 3600 AS varchar(2)),2) + ':'
										+ right('0' + CAST( ( ISNULL(@aldutybreaktime,0) % 3600 ) / 60 AS varchar(2)),2)+' To '
										+ right('0'+CAST( ISNULL(@vbreaktime,0) / 3600 AS varchar(2)),2) + ':'
										+ right('0' + CAST( ( ISNULL(@vbreaktime,0) % 3600 ) / 60 AS varchar(2)),2)+'.'

				SET @updateflag = 1

			 END

			 IF ( @vendtime < @vstarttime OR @vendtime >= 86400 )
			  BEGIN
			   SET @vSQL = @vSQL+' aftermidnight = 1, '
			  END	

			IF ( ISNULL(@vTimeChangeFlag,0) <> 0 )
			 BEGIN
				SET @updateflag = 1
				SET @alduration = case when @vendtime > @vstarttime then @vendtime - @vstarttime
										when @vendtime < @vstarttime then (86400 - @vstarttime)+ @vendtime end
				SET @vSQL = @vSQL+' duration = '+cast(@alduration as varchar)+', '
			 END

	  IF ( 	@vTimeChangeFlag = 1 OR @vDurationFlag = 1) 
	   BEGIN
	     
		 IF ( CAST(@AlDuration AS FLOAT)/CAST(3600 AS FLOAT) > 12 and ISNULL(@IsOverrideOver12,1) = 1 )
		  BEGIN
		    SET @vSQL = @vSQL+' MarkOverTwelve = -1, '
		  END
		 ELSE
		  BEGIN
		    SET @vSQL = @vSQL+' MarkOverTwelve = 9, '		  
		  END
		 
		SET @vSQL = @vSQL+' OverTwelveHrs = 0, IsOverseasOverTwelve = 0, '							
		 
	   END	
	   
	  IF ( 	isnull(@vTimeChangeFlag,0) <> 1 AND ISNULL(@vDurationFlag,0) <> 1 and @IsOverrideOver12UpdateFlag = 1) 
	   BEGIN
	     
		 IF ( CAST(@alduration AS FLOAT)/CAST(3600 AS FLOAT) > 12 and ISNULL(@IsOverrideOver12,1) = 0 )
		  BEGIN
		    SET @vSQL = @vSQL+' MarkOverTwelve = 9, '		  	  
		    SET @vSQL = @vSQL+' OverTwelveHrs = 0, IsOverseasOverTwelve = 0, '							
		  END

		 IF ( CAST(@AlDuration AS FLOAT)/CAST(3600 AS FLOAT) > 12 and ISNULL(@IsOverrideOver12,1) = 1
		      and  ISNULL(@MarkOverTwelve,9) = 9 )
		  BEGIN
		    SET @vSQL = @vSQL+' MarkOverTwelve = -1, '		  	  
		    SET @vSQL = @vSQL+' OverTwelveHrs = 0, IsOverseasOverTwelve = 0, '							
		  END
		 
	   END		   
	   	   
      IF ( @updateflag = 1)
	   BEGIN

		 SET @vSQL = @vSQL+' markactual = CASE WHEN IsHomeTeam IN (0,2) AND ISNULL(MarkWIAD,0) = 0 
						                      THEN 1 ELSE markactual END,
											  RecordAction =''U''
											  WHERE AllocationsDutyID = '+cast(@AllocationsDutyID as varchar)

				EXEC (@vSQL)

				INSERT INTO @TempHistory (   historytype,
										attributeid,
										HistorySubType,
										userid,
										history)
				SELECT @HistoryTypeDuty AS historytype,
					   @AllocationsDutyID AS attributeid,
					   NULL AS HistorySubType,
					   @vuserID,
					   @vHistory

			IF ( @MarkOverTwelve = 1 AND ( @vTimeChangeFlag = 1 OR @vDurationFlag = 1) )
			 BEGIN

				INSERT INTO @TempHistory ( historytype,
						  attributeid,
						  HistorySubType,
						  userid,
						  history )		
				SELECT @HistoryTypePerson AS historytype,
					   @AllocationsSPID AS attributeid,
					   'PH' AS HistorySubType,
					   @vuserID,
					   'Over12 was Removed by system '+
					   + ' On ' + FORMAT(Getdate(),'dd/MM/yyyy')+' at '
						+ FORMAT(Getdate(),'HH:mm')+'.'

			 
			 END	

		   END


		   IF ( @updateflag = 0)
	         BEGIN

			   UPDATE #TempAllocations 
			      SET RecordAction = 'N'
				WHERE AllocationsDutyID = @AllocationsDutyID

			 END
			 

			SET @updateflag = 0
			SET @vTimeChangeFlag = 0
			SET @vDurationFlag = 0
			SET @IsOverrideOver12UpdateFlag = 0

			FETCH NEXT FROM CUR_Upd_Duty INTO
							@aldutyname,
							@alstarttime,
							@alendtime,
							@aldutybreaktime,
							@aldutycolorid,
							@alduration,
							@alid,
							@AllocationsDutyID,
							@AllocationsSPID,
							@aldutydate,
							@dutyProgramId,
							@dutyprogramID2,
							@dutyprogramID3,
							@dutyprogramID4,
							@dutyprogramID5,
							@dutyprogramID6,
							@IsNeedCovering,
							@IsOverrideOver12,
							@isDutyEditedPostWeek,
							@MarkedOvertime,
							@isRequest,
							@ChargingStatus,
							@isAttention,
							@LeaveStartTime,
							@LeaveEndTime,
							@MarkOverTwelve

		END

		CLOSE CUR_Upd_Duty;

		DEALLOCATE CUR_Upd_Duty;

	  END

	-- Complete the action

				 UPDATE AL
					SET al.AD_DutyName = tal.dutyname,
						al.AD_IsNeedCovering = tal.IsNeedCovering,
						al.AD_IsOverrideOver12 = tal.IsOverrideOver12,
						al.AD_DutyProgramID1 = tal.dutyProgramId,
						al.AD_DutyProgramID2 = tal.dutyProgramId2,
						al.AD_DutyProgramID3 = tal.dutyProgramId3,
						al.AD_DutyProgramID4 = tal.dutyProgramId4,
						al.AD_DutyProgramID5 = tal.dutyProgramId5,
						al.AD_DutyProgramID6 = tal.dutyProgramId6,
						al.AD_DutyColourID = tal.dutyColorId,
						al.AD_Duration = tal.duration,
						al.AD_StartTimeSec = tal.StartTime,
						al.AD_DutyStartTimeLocal = tal.StartDate,
						al.AD_EndTimeSec = tal.EndTime,
						al.AD_DutyEndTimeLocal = tal.EndDate,
						al.AD_DutyStartTimeUTC = tal.StartDate,
						al.AD_DutyEndTimeUTC = tal.EndDate,
						al.AD_DutyBreakTime = tal.dutyBreakTime,					
						al.AD_UpdatedBy = @vuserID, 
						al.AD_UpdatedDate = getutcdate()	
			      FROM  AllocationsDuties AL 
			     INNER JOIN #TempAllocations TAL ON TAL.AllocationsDutyID = AL.AD_AllocationsDutyID
				 WHERE TAL.RecordAction = 'U'

			   UPDATE AD
			      SET AD.AD_isAttention = 2
			     FROM AllocationsDuties AD
				INNER JOIN #TempAllocations tal ON AD.AD_AllocationsDutyID = TAL.AllocationsDutyID
				WHERE RecordAction = 'X'

				UPDATE ASP
				   SET ASP_OverTwelveStatus = case when TAL.duration > 43200
						                        and isnull(TAL.IsOverrideOver12,1) = 1
						                        then 1 else 0 end,
					   ASP_OverTimeHours = 0,
					   ASP_IsOverseasOverTwelve = 0,
					   ASP_UpdatedBy = @vuserID,
					   ASP_UpdatedDate = getutcdate()
				 FROM AllocationsScheduledPersons ASP
				INNER JOIN #TempAllocations TAL ON TAL.AllocationsSPID = ASP_AllocationsSPID
				 WHERE TAL.RecordAction = 'U'

			INSERT INTO history ( historytype,
								  attributeid,
								  HistorySubType,
								  datetime,
								  userid,
								  history )	
						SELECT  @HistoryTypeDuty,
								  AllocationsDutyID AS attributeid,
								  'DH' HistorySubType,
								  getdate(),
								  @vuserID,
								  'The Master Duty has been amended but due to edits made, this Duty has not been.'
						FROM #TempAllocations 
					   WHERE RecordAction ='X'

			INSERT INTO history ( historytype,
								  attributeid,
								  HistorySubType,
								  datetime,
								  userid,
								  history )	
						SELECT  historytype,
								  attributeid,
								  HistorySubType,
								  getdate(),
								  userid,
								  history
						FROM @TempHistory 

	-- Delete job which are removed from forward planning start
	BEGIN

		 SELECT AJ_AllocateJobID AS ID, 'D' AS RecordType
		   INTO #TempAllocationsJobs
		   FROM Allocationsjobs AJ (NOLOCK)
		  INNER JOIN #TempAllocations AL (NOLOCK) ON AL.AllocationsDutyID = AJ.AJ_AllocationsDutyID
		  WHERE AJ_MasterJobID not in (
										SELECT MJ.masterjobid
										  FROM MasterJobs MJ (NOLOCK)
										 INNER JOIN MasterDutiesMasterJobs_LINK as mdmj (NOLOCK) on MJ.MasterJobID = mdmj.MasterJobID
										 WHERE MJ.IsActive=1
										   and mdmj.MasterDutyID = @pDutyID   
										)
			AND AL.RecordAction IN ('U','N')

		UPDATE Allocationsjobs
		   SET AJ_JobStatus = 9
		 WHERE AJ_AllocateJobID IN (  SELECT ID 
										FROM #TempAllocationsJobs 
									   WHERE RecordType ='D'
									)

	END

	-- Delete job which are removed from forward planning End

	-- Create job which are added from forward planning Start

	BEGIN

	  DELETE FROM #TempAllocationsJobs

		INSERT INTO #TempAllocationsJobs
		SELECT AJ1.AJ_AllocateJobID AS ID, 'O' AS RecordType
		  FROM Allocationsjobs AJ1
		 INNER JOIN #TempAllocations AL1 (NOLOCK) ON AL1.AllocationsDutyID = AJ1.AJ_AllocationsDutyID
		 INNER JOIN MasterJobs MJ1 (NOLOCK) ON MJ1.masterjobid = AJ1.AJ_MasterJobID
		 INNER JOIN MasterDutiesMasterJobs_LINK as mdmj1 (NOLOCK) on MJ1.MasterJobID = mdmj1.MasterJobID
		                            AND mdmj1.MasterDutyID = al1.MasterDutyId
		 WHERE MJ1.IsActive = 1
		   AND al1.RecordAction IN ('U','N')

	  INSERT INTO [dbo].[AllocationsJobs]
			(     AJ_AllocationsDutyID,
					AJ_MasterJobID,
					AJ_ProgrammeID,
					AJ_Contact,
					AJ_Location,
					AJ_JobName,
					AJ_JobStartTimeSec,
					AJ_JobEndTimeSec,
					AJ_JobStartTimeUTC,
					AJ_JobEndTimeUTC,
					AJ_JobStartTimeLocal,
					AJ_JobEndTimeLocal,
					AJ_JobBGColour,
					AJ_JobFontColour,
					AJ_Comments,
					AJ_JobStatus,
					AJ_JobInfo,
					AJ_CreatedBy,
					AJ_CreatedDate
			)
		OUTPUT INSERTED.AJ_AllocateJobID INTO @TempJobList
		SELECT AL.AllocationsDutyID,
			   MJ.MasterJobID,
			   PG.ID,
			   MJ.Contact,
			   MJ.[Location],
			   MJ.JobName,
			   case when MJ.starttime >= 86400 then (MJ.starttime - 86400) else MJ.starttime end as starttime,
			   case when MJ.endtime >= 86400 then (MJ.endtime - 86400) else MJ.endtime end as endtime,
				   CASE
					 WHEN MJ.starttime = 0 THEN AL.DutyDate
					 WHEN MJ.starttime = 86400 THEN DATEADD(DAY,1,AL.DutyDate)
					 WHEN MJ.starttime > 86400 THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,AL.DutyDate),MJ.starttime - 86400 )
					 ELSE dbo.ufn_ConvertToDateTime(AL.DutyDate,MJ.starttime ) END AS StartDate,
				   CASE
					 WHEN MJ.StartTime > 0 AND MJ.EndTime = 0 THEN DATEADD(DAY,1,AL.DutyDate)
					 WHEN MJ.EndTime > 86400 THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,AL.DutyDate),MJ.EndTime - 86400)
					 WHEN MJ.EndTime = 86400 THEN DATEADD(DAY,1,AL.DutyDate)
					 ELSE dbo.ufn_ConvertToDateTime(AL.DutyDate,MJ.EndTime) END AS EndDate,	
				   CASE
					 WHEN MJ.starttime = 0 THEN AL.DutyDate
					 WHEN MJ.starttime = 86400 THEN DATEADD(DAY,1,AL.DutyDate)
					 WHEN MJ.starttime > 86400 THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,AL.DutyDate),MJ.starttime - 86400)
					 ELSE dbo.ufn_ConvertToDateTime(AL.DutyDate,MJ.starttime ) END AS StartDateLocal,
				    CASE
					 WHEN MJ.StartTime > 0 AND MJ.EndTime = 0 THEN DATEADD(DAY,1,AL.DutyDate)
					 WHEN MJ.EndTime > 86400 THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,AL.DutyDate),MJ.EndTime - 86400)
					 WHEN MJ.EndTime = 86400 THEN DATEADD(DAY,1,AL.DutyDate)
					 ELSE dbo.ufn_ConvertToDateTime(AL.DutyDate,MJ.EndTime) END AS EndDateLocal,	
			   JC.ColourBackground as jobbackcolour,
			   JC.ColourFont as jobfontcolour,
			   NULL,
			   1 as AJ_JobStatus,
			   mj.Details as job_info,
			   @vuserID,
			   GETUTCDATE()
		FROM MasterJobs MJ (NOLOCK)
       INNER JOIN MasterDutiesMasterJobs_LINK as mdmj (NOLOCK) on MJ.MasterJobID = mdmj.MasterJobID
       INNER JOIN #TempAllocations AL (NOLOCK) on AL.MasterDutyId=mdmj.MasterDutyID and al.SchedulingTeamId = mj.TeamID
        LEFT JOIN LINK_MasterJobs_Programmes MLP (NOLOCK) ON MJ.MasterJobID=MLP.MasterJobID
        LEFT JOIN Programmes PG (NOLOCK) ON MLP.ProgrammeID=PG.ID
        LEFT JOIN REF_MasterJobColours JC (NOLOCK) ON MJ.MasterJobID=JC.MasterJobID and JC.IsActive=1
	   WHERE MJ.IsActive=1
	     AND al.RecordAction IN ('U','N')
		 AND NOT EXISTS ( SELECT 1
						   FROM Allocationsjobs AJ1
						  INNER JOIN #TempAllocations AL1 (NOLOCK) ON AL1.AllocationsDutyID = AJ1.AJ_AllocationsDutyID
						  INNER JOIN MasterJobs MJ1 (NOLOCK) ON MJ1.masterjobid = AJ1.AJ_MasterJobID
						  INNER JOIN MasterDutiesMasterJobs_LINK as mdmj1 (NOLOCK) on MJ1.MasterJobID = mdmj1.MasterJobID
						  WHERE al1.RecordAction IN ('U','N')
							AND MJ1.IsActive = 1
							AND mdmj1.MasterDutyID = @pDutyID
							AND AL1.AllocationsDutyID = AL.AllocationsDutyID
							AND MJ1.masterjobid = MJ.masterjobid
						 )


		IF ( @@ROWCOUNT > 0 )
		 BEGIN

			INSERT INTO history
			(
				historytype,
				attributeid,
				datetime,
				userid,
				history
			)
			SELECT     @HistoryTypeJob AS historytype,
					   JobID AS attributeid,
					   getdate(),
					   @vuserID,
					   'New Job created by '+@vname+' On '++ FORMAT(Getdate(),'dd/MM/yyyy HH:mm')+'.'
		     FROM @TempJobList

		END

	 END

	 BEGIN


		 UPDATE AJ
			SET AJ.AJ_JobName = MJ.JobName,
				AJ.AJ_JobStartTimeSec = case when MJ.starttime >= 86400 then (MJ.starttime - 86400) else MJ.starttime end,
				AJ.AJ_JobEndTimeSec = case when MJ.endtime >= 86400 then (MJ.endtime - 86400) else MJ.endtime end,
				AJ.AJ_JobStartTimeUTC =	CASE
						 WHEN MJ.starttime = 0 THEN AL.DutyDate
						 WHEN MJ.starttime = 86400 THEN DATEADD(DAY,1,AL.DutyDate)
						 WHEN MJ.starttime > 86400 THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,AL.DutyDate),MJ.starttime - 86400 )
						 ELSE dbo.ufn_ConvertToDateTime(AL.DutyDate,MJ.starttime ) END,
				AJ_JobEndTimeUTC = CASE
						 WHEN MJ.StartTime > 0 AND MJ.EndTime = 0 THEN DATEADD(DAY,1,AL.DutyDate)
						 WHEN MJ.EndTime > 86400 THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,AL.DutyDate),MJ.EndTime - 86400 )
						 WHEN MJ.EndTime = 86400 THEN DATEADD(DAY,1,AL.DutyDate)
						 ELSE dbo.ufn_ConvertToDateTime(AL.DutyDate,MJ.EndTime) END,	
				AJ_JobStartTimeLocal =  CASE
						 WHEN MJ.starttime = 0 THEN AL.DutyDate
						 WHEN MJ.starttime = 86400 THEN DATEADD(DAY,1,AL.DutyDate)
						 WHEN MJ.starttime > 86400 THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,AL.DutyDate),MJ.starttime - 86400  )
						 ELSE dbo.ufn_ConvertToDateTime(AL.DutyDate,MJ.starttime ) END,
				AJ_JobEndTimeLocal =  CASE
						 WHEN MJ.StartTime > 0 AND MJ.EndTime = 0 THEN DATEADD(DAY,1,AL.DutyDate)
						 WHEN MJ.EndTime > 86400 THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,AL.DutyDate),MJ.EndTime - 86400 )
						 WHEN MJ.EndTime = 86400 THEN DATEADD(DAY,1,AL.DutyDate)
						 ELSE dbo.ufn_ConvertToDateTime(AL.DutyDate,MJ.EndTime) END,	
				AJ.AJ_JobBGColour = JC.ColourBackground,
				AJ.AJ_JobFontColour = JC.ColourFont,
				AJ.AJ_ProgrammeID = PG.ID,
				AJ.AJ_JobInfo = mj.Details
		 FROM AllocationsJobs AJ (NOLOCK)
		INNER JOIN MasterJobs MJ (NOLOCK) ON AJ.AJ_MasterJobID = MJ.masterjobid
		INNER JOIN MasterDutiesMasterJobs_LINK as mdmj (NOLOCK) on MJ.MasterJobID = mdmj.MasterJobID
		INNER JOIN #TempAllocations AL (NOLOCK) on AL.AllocationsDutyID = AJ.AJ_AllocationsDutyID 
												AND AL.MasterDutyId=mdmj.MasterDutyID
												AND al.SchedulingTeamId = mj.TeamID
		 LEFT JOIN LINK_MasterJobs_Programmes MLP (NOLOCK) ON MJ.MasterJobID=MLP.MasterJobID
		 LEFT JOIN Programmes PG (NOLOCK) ON MLP.ProgrammeID=PG.ID
		 LEFT JOIN REF_MasterJobColours JC (NOLOCK) ON MJ.MasterJobID=JC.MasterJobID AND JC.IsActive=1
		WHERE MJ.IsActive=1
		  AND al.RecordAction IN ('U','N')

	 END
	
	 -- Update Job details completed

	 -- Correct the duty count in allocation based on forward planning start

	 BEGIN

	   DECLARE CUR_Pend_Duty CURSOR FOR
		SELECT *
		  FROM (
				 SELECT ISNULL(FMD.WeekNumber,MDR.WeekNumber)              AS WeekNumber,
						ISNULL(FMD.masterdutyid,mdr.MasterDutyID)          AS MasterDutyID,
						ISNULL(FMD.iday,MDR.iday)                          AS iDay,
						ISNULL(FMD.SchedulingTeamID,MDR.SchedulingTeamID)  AS SchedulingTeamID ,
						ISNULL(FMD.AllocationID,MDR.AllocationsID)          AS AllocationID,
						case when fmd.dutycount - Isnull(mdr.dutycount, 0) > 0
								 then fmd.dutycount - Isnull(mdr.dutycount, 0)
							 else 0 end as createdutycnt,
						case when ISNULL(fmd.dutycount,0) - Isnull(mdr.dutycount, 0) < 0
								 then ( mdr.dutycount - ISNULL(fmd.dutycount,0) )
							 else 0 end as removedutycnt
				   FROM ( SELECT masterdutyid,
								 CASE
									WHEN iday = 'Saturday' THEN 0
									WHEN iday = 'Sunday' THEN 1
									WHEN iday = 'Monday' THEN 2
									WHEN iday = 'Tuesday' THEN 3
									WHEN iday = 'Wednesday' THEN 4
									WHEN iday = 'Thursday' THEN 5
									ELSE 6
									END AS IDAY,
								dutycount,
								AP.AL_WeekNumber WeekNumber,
								AP.AL_SchedulingTeamID SchedulingTeamId,
								AP.AL_AllocationsID AllocationID
						   FROM ( SELECT masterdutyid,
										StartWeek,
										EndWeek,
										saturday,
										sunday,
										monday,
										tuesday,
										wednesday,
										thursday,
										friday
								   FROM MasterDuties (NOLOCK)
								  WHERE MasterDutyID = @pDutyID
								    AND @pweekNumber BETWEEN startweek AND endweek 
								 ) MD
                        UNPIVOT (dutycount
								 FOR iday IN (  saturday,
												sunday,
												monday,
												tuesday,
												wednesday,
												thursday,
												friday) 
								 ) AS md1
					    INNER JOIN Allocations AP ON 1=1
                     AND AP.AL_SchedulingTeamID = @pTeamID
                     AND AP.AL_WeekNumber >= @pweekNumber
                     AND AP.AL_WeekNumber <= EndWeek
                   WHERE dutycount > 0) AS FMD
                    FULL JOIN (SELECT AP.AL_SchedulingTeamID AS SchedulingTeamID,
                                      AP.AL_WeekNumber	AS WeekNumber,
                                      AL.iday,
                                      MD.masterdutyid,
                                      AP.AL_AllocationsID	AS AllocationsID,
                                      Count(1) DutyCount
                                 FROM #TempAllocations AL (NOLOCK)
                                inner join MasterDuties MD (NOLOCK) ON AL.masterdutyid = MD.masterdutyid
                                inner join Allocations AP (NOLOCK) ON al.WeekNumber = ap.AL_WeekNumber and al.SchedulingTeamId = ap.AL_SchedulingTeamID
								WHERE ap.AL_WeekNumber between md.startweek AND md.endweek
								group by AP.AL_SchedulingTeamID,
								      ap.AL_WeekNumber, 
									  al.iday, 
									  md.MasterDutyID,
									  AP.AL_AllocationsID
			 ) MDR ON MDR.masterdutyid=fmd.masterdutyid
				 AND MDR.iday = FMD.IDAY
				 AND MDR.WeekNumber = FMD.WeekNumber
				 AND MDR.SchedulingTeamID = FMD.SchedulingTeamID
		 ) FMstrData WHERE ( removedutycnt > 0 OR createdutycnt > 0 )

		OPEN CUR_Pend_Duty

		FETCH NEXT FROM CUR_Pend_Duty INTO  @vWeekNumber,
											@vMasterDuty,
											@viday,
											@vTeamID,
											@vAllocationID,
											@createdutycnt,
											@removedutycnt

    WHILE @@FETCH_STATUS = 0
	 BEGIN

		WHILE @createdutycnt > 0
		 BEGIN

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
				AD_CreatedBy,
				AD_CreatedDate				 
				 )
          SELECT @vAllocationID AS allocationid,
                 MD.dutyname,
                 case when isnull(MD.duration,0) = 0 then 
                 case when MD.endtime > MD.starttime then MD.endtime- MD.starttime
                      when MD.endtime < MD.starttime then (86400-MD.starttime)+MD.endtime end
                      else  MD.duration end as duration,
                 case when isnull(MD.duration,0) = 0 then 
                 case when MD.endtime > MD.starttime then MD.endtime- MD.starttime
                      when MD.endtime < MD.starttime then (86400-MD.starttime)+MD.endtime end
                      else  MD.duration end AS PlannedDuration,
               TD3.iday,
               case when MD.StartTime >= 86400 then ( MD.StartTime - 86400) else MD.StartTime end AS starttime,
               case when MD.EndTime >= 86400 then (MD.EndTime - 86400) else MD.EndTime end AS endtime,
               MD.DutyComment,
               TD3.dutydate,
			   CASE
				 WHEN md.starttime IS NULL THEN NULL
				 WHEN MD.StartTime = 0 AND MD.EndTime = 0 THEN NULL
				 WHEN md.starttime = 0 AND md.EndTime > 0 THEN TD3.DutyDate
				 WHEN md.starttime > 0 THEN dbo.ufn_ConvertToDateTime(TD3.DutyDate,md.starttime)
				 ELSE dbo.ufn_ConvertToDateTime(TD3.DutyDate,md.starttime) END AS StartDateUTC,
			   CASE
				 WHEN md.endtime IS  NULL THEN NULL
				 WHEN MD.StartTime = 0 AND MD.EndTime = 0 THEN NULL
				 WHEN MD.StartTime > 0 AND md.EndTime = 0 THEN DATEADD(DAY,1,TD3.DutyDate)
				 WHEN md.endtime = 86400 THEN DATEADD(DAY,1,TD3.DutyDate)
				 WHEN md.endtime > 86400 THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,TD3.DutyDate),md.endtime-86400)
				 WHEN MD.endtime < MD.StartTime THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,TD3.DutyDate),md.endtime)
				 ELSE dbo.ufn_ConvertToDateTime(TD3.DutyDate,md.EndTime) END AS EndDateUTC,
               CASE
				 WHEN md.starttime IS NULL THEN NULL
				 WHEN MD.StartTime = 0 AND MD.EndTime = 0 THEN NULL
				 WHEN md.starttime = 0 AND md.EndTime > 0 THEN TD3.DutyDate
				 WHEN md.starttime > 0 THEN dbo.ufn_ConvertToDateTime(TD3.DutyDate,md.starttime)
				 ELSE dbo.ufn_ConvertToDateTime(TD3.DutyDate,md.starttime) END AS StartDateLocal,
               CASE
				 WHEN md.endtime IS  NULL THEN NULL
				 WHEN MD.StartTime = 0 AND MD.EndTime = 0 THEN NULL
				 WHEN MD.StartTime > 0 AND md.EndTime = 0 THEN DATEADD(DAY,1,TD3.DutyDate)
				 WHEN md.endtime = 86400 THEN DATEADD(DAY,1,TD3.DutyDate)
				 WHEN md.endtime > 86400 THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,TD3.DutyDate),md.endtime-86400)
				 WHEN MD.endtime < MD.StartTime THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,TD3.DutyDate),md.endtime)
				 ELSE dbo.ufn_ConvertToDateTime(TD3.DutyDate,md.EndTime) END AS EndDateLocal,
				 MD.BreakTime,
				 MD.MasterDutyID,
				 6 as DutyTypeID,
				 0 as DutyStatus,
                 MD.dutycolourid,
                 0,
                 0,
			     MD.BreakTime as PlannedDutyBreakTime,
				 MD.IsNeedCovering,
				 MD.IsOverrideOver12,
				 0,
				 md.DutyProgramId1,
				 md.DutyProgramId2,
				 md.DutyProgramId3,
				 md.DutyProgramId4,
				 md.DutyProgramId5,
				 md.DutyProgramId6,
                 @vuserID, 
                 getutcdate()
            FROM MasterDuties MD,
					(select td.dDateTime dutydate,
							td.ixYearWeek weeknumber ,
							td.ixDayInWeek iday
					 from TimeDimension td (NOLOCK)
					 where td.ixYearWeek = @vWeekNumber
					   and td.ixDayInWeek= @viday ) TD3
			where md.MasterDutyID=@vMasterDuty
			  AND @vWeekNumber BETWEEN startweek AND endweek

			INSERT INTO #TempAllocationList
			VALUES (@@IDENTITY, 'C')

			SET @createdutycnt = @createdutycnt - 1

			END

			BEGIN


				SET @vSQL = 'INSERT INTO #TempAllocationList
						        SELECT TOP '+cast(@removedutycnt as varchar)+' AD_AllocationsDutyID AS ID, ''D'' as ActionType
						            FROM Allocations AL
								INNER JOIN AllocationsDuties AD ON AL_AllocationsID = AD_AllocationsID
						        WHERE AL.AL_WeekNumber = '+cast(@vWeekNumber as varchar)
						        +' AND AD_MasterDutyID = '+cast(@vMasterDuty as varchar)
						        +' AND AD_iDAY = '+cast(@viday as varchar)
						        +' AND AL_SchedulingTeamID = '+cast(@vTeamID as varchar)
						        +' AND AD_DutyStatus = 0 '

				EXEC ( @vSQL )

		   END

		   FETCH NEXT FROM CUR_Pend_Duty INTO
							@vWeekNumber,
							@vMasterDuty,
							@viday,
							@vTeamID,
							@vAllocationID,
							@createdutycnt,
							@removedutycnt

		 END

		CLOSE CUR_Pend_Duty;

		DEALLOCATE CUR_Pend_Duty;

		END

		UPDATE AJ
		   SET AJ.AJ_JobStatus = 9
 		  FROM #TempAllocationList TL
		 INNER JOIN AllocationsJobs AJ ON TL.ID = AJ.AJ_AllocationsDutyID
		 WHERE TL.ActionType = 'D' 

		UPDATE AD
		   SET AD_DutyStatus = 9
		  FROM #TempAllocationList TL
		 INNER JOIN AllocationsDuties AD on TL.ID = AD.AD_AllocationsDutyID  
		 WHERE ActionType = 'D' 

		-- Correct the duty count in allocation based on forward planning start
        -- Create assigned job start

	  INSERT INTO [dbo].[AllocationsJobs]
			(     AJ_AllocationsDutyID,
					AJ_MasterJobID,
					AJ_ProgrammeID,
					AJ_Contact,
					AJ_Location,
					AJ_JobName,
					AJ_JobStartTimeSec,
					AJ_JobEndTimeSec,
					AJ_JobStartTimeUTC,
					AJ_JobEndTimeUTC,
					AJ_JobStartTimeLocal,
					AJ_JobEndTimeLocal,
					AJ_JobBGColour,
					AJ_JobFontColour,
					AJ_Comments,
					AJ_JobStatus,
					AJ_JobInfo,
					AJ_CreatedBy,
					AJ_CreatedDate
			)
		OUTPUT INSERTED.AJ_AllocateJobID INTO @TempJobList
		SELECT AL.AD_AllocationsDutyID,
			   MJ.MasterJobID,
			   PG.ID,
			   MJ.Contact,
			   MJ.[Location],
			   MJ.JobName,
			   case when MJ.starttime >= 86400 then (MJ.starttime - 86400) else MJ.starttime end as starttime,
			   case when MJ.endtime >= 86400 then (MJ.endtime - 86400) else MJ.endtime end as endtime,
				   CASE
					 WHEN MJ.starttime = 0 THEN AL.AD_DutyDate
					 WHEN MJ.starttime = 86400 THEN DATEADD(DAY,1,AL.AD_DutyDate)
					 WHEN MJ.starttime > 86400 THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,AL.AD_DutyDate),MJ.starttime - 86400 )
					 ELSE dbo.ufn_ConvertToDateTime(AL.AD_DutyDate,MJ.starttime ) END AS StartDate,
				   CASE
					 WHEN MJ.StartTime > 0 AND MJ.EndTime = 0 THEN DATEADD(DAY,1,AL.AD_DutyDate)
					 WHEN MJ.EndTime > 86400 THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,AL.AD_DutyDate),MJ.EndTime - 86400 )
					 WHEN MJ.EndTime = 86400 THEN DATEADD(DAY,1,AL.AD_DutyDate)
					 ELSE dbo.ufn_ConvertToDateTime(AL.AD_DutyDate,MJ.EndTime) END AS EndDate,	
				   CASE
					 WHEN MJ.starttime = 0 THEN AL.AD_DutyDate
					 WHEN MJ.starttime = 86400 THEN DATEADD(DAY,1,AL.AD_DutyDate)
					 WHEN MJ.starttime > 86400 THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,AL.AD_DutyDate),MJ.starttime - 86400 )
					 ELSE dbo.ufn_ConvertToDateTime(AL.AD_DutyDate,MJ.starttime ) END AS StartDateLocal,
				    CASE
					 WHEN MJ.StartTime > 0 AND MJ.EndTime = 0 THEN DATEADD(DAY,1,AL.AD_DutyDate)
					 WHEN MJ.EndTime > 86400 THEN dbo.ufn_ConvertToDateTime(DATEADD(DAY,1,AL.AD_DutyDate),MJ.EndTime - 86400 )
					 WHEN MJ.EndTime = 86400 THEN DATEADD(DAY,1,AL.AD_DutyDate)
					 ELSE dbo.ufn_ConvertToDateTime(AL.AD_DutyDate,MJ.EndTime) END AS EndDateLocal,	
			   JC.ColourBackground as jobbackcolour,
			   JC.ColourFont as jobfontcolour,
			   NULL,
			   1 as AJ_JobStatus,
			   mj.Details as job_info,
			   @vuserID,
			   GETUTCDATE()
			FROM MasterJobs MJ (NOLOCK)
		   INNER JOIN MasterDutiesMasterJobs_LINK as mdmj (NOLOCK) on MJ.MasterJobID = mdmj.MasterJobID
		   INNER JOIN AllocationsDuties AL (NOLOCK) on AL.AD_MasterDutyID = mdmj.MasterDutyID
			LEFT JOIN LINK_MasterJobs_Programmes MLP (NOLOCK) ON MJ.MasterJobID=MLP.MasterJobID
			LEFT JOIN Programmes PG (NOLOCK) ON MLP.ProgrammeID=PG.ID
			LEFT JOIN REF_MasterJobColours JC (NOLOCK) ON MJ.MasterJobID=JC.MasterJobID and JC.IsActive=1
			WHERE MJ.IsActive=1
			  AND MJ.TeamID = @pteamId
			  AND AL.AD_AllocationsDutyID in ( SELECT ID FROM #TempAllocationList WHERE ActionType = 'C' )

	  -- Create assigned job End

	  -- Create Allocation history Start

		INSERT INTO history
		(
			historytype,
			attributeid,
			HistorySubType,
			datetime,
			userid,
			history
		)
		SELECT @HistoryTypeDuty AS historytype,
			   al.AD_AllocationsDutyID AS attributeid,
			   'DH'  AS HistorySubType,
			   getdate(),
			   @vuserID,
			   'Created '
				   + ' On ' + FORMAT(Getdate(),'dd/MM/yyyy') + ' '
				   + FORMAT(Getdate(),'HH:mm')
				   + ' By ' + @vname + '. Duty: ' + AD_DutyName
		FROM  AllocationsDuties AL (NOLOCK)
		WHERE AL.AD_AllocationsDutyID in ( SELECT ID FROM #TempAllocationList WHERE ActionType = 'C' )

      -- Create Allocation history End

      -- Create Job history Start


		INSERT INTO history
	    (
			historytype,
			attributeid,
			datetime,
			userid,
			history
	    )
	    SELECT @HistoryTypeJob AS historytype,
			   aj.AJ_AllocateJobID AS attributeid,
			   getdate(),
			   @vuserID,
			   'New Job created by '+@vname+' On '+FORMAT(Getdate(),'dd/MM/yyyy HH:mm')+'.'
	      FROM AllocationsJobs aj (NOLOCK)
		 INNER JOIN AllocationsDuties al (NOLOCK) ON al.AD_AllocationsDutyID=aj.AJ_AllocationsDutyID
	     WHERE AL.AD_AllocationsDutyID in ( SELECT ID 
		                    FROM #TempAllocationList 
						   WHERE ActionType = 'C' 
						 )

		 DECLARE CUR_DutySummary CURSOR FOR
		  SELECT MIN(AL.DutyDate),
				 MAX(AL.DutyDate),
				 AL.SchedulingPersonID
		    FROM #TempAllocations AL 
		   WHERE AL.RecordAction IN ('U','N')
		     AND AL.SchedulingPersonID > 0
		   GROUP BY AL.SchedulingPersonID

		   OPEN CUR_DutySummary

		  FETCH NEXT FROM CUR_DutySummary INTO  @StartDate,@EndDate,@SchedulingPersonID

		  WHILE @@FETCH_STATUS = 0
			BEGIN

				EXEC @ReturnValue = usp_Create_DutyAccPeriodSummarySP @StartDate,@EndDate,@SchedulingPersonID, @pNetLogin

				FETCH NEXT FROM CUR_DutySummary INTO  @StartDate,@EndDate,@SchedulingPersonID

			END

		CLOSE CUR_DutySummary;

		DEALLOCATE CUR_DutySummary;

      -- Create Job history End

    IF ( @@TRANCOUNT	> 0 )
	 BEGIN
		COMMIT  TRANSACTION
	 END

		SELECT 0 AS SPExecStatus,
				'Copied to all duties : ('
				   + cast(@vdutyname as varchar)
				   +') Starting From Week Number '
				   +SUBSTRING(cast(@pweekNumber as varchar), 5, 6)+'/'
				   +SUBSTRING(cast(@pweekNumber as varchar), 1, 4)
				   AS SPMessage

     SELECT DutyName, 
	        DutyDate,
			StartTime,
			EndTime,
			st.schedulingTeamName
	   FROM #TempAllocations TL
	  inner join schedulingTeams st on st.schedulingTeamId = tl.SchedulingTeamId
	  where tl.isAttention = 2


	END TRY
				
	BEGIN CATCH

	  IF ( @@TRANCOUNT  > 0 ) 
	   BEGIN
		ROLLBACK TRANSACTION
	   END

	   IF ( ERROR_NUMBER() < 50000 )
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

       IF ( ERROR_NUMBER() in (1204,1205,1222,3930) )
		 SELECT 'Somebody else is also editing this duty. Please try again' AS errorMessage, 0 spStatus
	    ELSE 
	 	 SELECT ISNULL(@@IDENTITY,ERROR_NUMBER() ) as SPExecStatus,
			    ERROR_MESSAGE() as SPMessage  

	END CATCH;	

END