USE [BBCSchedules_WP]
GO
/****** Object:  StoredProcedure [dbo].[usp_CreateScheduledPerson]    Script Date: 30/03/2026 14:44:06 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER             PROCEDURE  [dbo].[usp_CreateScheduledPerson]
@AllocationsID			INT,
@AllocationsDutyID		INT,
@SchedulingpersonID		INT,
@DutyDate				DATE,
@pNetLogin				VARCHAR(30),
@AllocationsSPID        INT OUTPUT

AS
BEGIN

    SET NOCOUNT ON

    SET DATEFORMAT YMD

	DECLARE   @vname				VARCHAR(100),
			  @DisplayName			VARCHAR(100),
			  @vuserID				INT,
			  @vErrorMsg			VARCHAR(1000),
			  @vSPStatus			INT,
			  @IsFreeLancer			BIT = 0,
			  @FLAllocationsID		INT,
			  @ASPID				INT,
			  @SortCode				NVARCHAR(30);

	BEGIN TRY

	  IF ( ISDATE(cast(@DutyDate as varchar) ) <> 1  OR @DutyDate = '') RETURN 1
	  IF (ISNULL(@SchedulingpersonID,0) = 0) RETURN 1

		SELECT @ASPID = ASP_AllocationsSPID
		FROM AllocationsScheduledPersons
		WHERE ASP_SchedulingPersonID = @SchedulingpersonID
		AND ASP_DutyDate = @DutyDate

		IF ( ISNULL( @ASPID,0 ) > 0 )
		 BEGIN
		   SET @AllocationsSPID = @ASPID
		   RETURN 0
		 END

	   SET @IsFreeLancer = dbo.ufn_IsFreeLancer(@SchedulingpersonID,@DutyDate)

	   SELECT @vname =  UD_DisplayName,
			  @vuserID = UD_UserID
	     from UserDetails
		WHERE UD_NetLogin = @pNetLogin

		SELECT @DisplayName = UD_DisplayName
	      from UserDetails
		 WHERE UD_UserID = @SchedulingpersonID

	 IF ( ISNULL(@AllocationsDutyID,0) = 0 AND @IsFreeLancer = 0)
	  BEGIN

	    SELECT @AllocationsDutyID = AD_AllocationsDutyID
		 FROM AllocationsDuties AD
		WHERE AD.AD_AllocationsID = @AllocationsID
		  AND AD.AD_DutyDate = @DutyDate
		  AND AD_DutyType = 7

	  END

	 IF ( @IsFreeLancer = 1 )
	  BEGIN

	     SELECT @FLAllocationsID = AL_AllocationsID
		   FROM Allocations AL
		  INNER JOIN ScheduledPersonTeam_LINK SL on SL.TeamID = AL.AL_SchedulingTeamID
		  INNER JOIN TimeDimension TD on td.ixYearWeek = AL_WeekNumber
		  where td.dDateTime = @DutyDate
		    and td.dDateTime between sl.StartDate and sl.EndDate
			and sl.scheduledType = 1
			and sl.IsHomeTeam = 1
			and AL_Status = 2
			and SL.ScheduledPersonID = @SchedulingpersonID

		 IF ( ISNULL(@FLAllocationsID,0) = 0 )
		   BEGIN

			 INSERT INTO  Allocations
					( AL_WeekNumber,
					  AL_SchedulingTeamId,
					  AL_Status,
					  AL_CreatedBy,
					  AL_CreatedDate)
			 SELECT td.ixYearWeek,
					SL.Teamid,
					2,
					@vuserID,
					getutcdate()
			   FROM ScheduledPersonTeam_LINK SL
			  INNER JOIN TimeDimension TD on 1 = 1
			  where td.dDateTime = @DutyDate
				and td.dDateTime between sl.StartDate and sl.EndDate
				and sl.scheduledType = 1
				and sl.IsHomeTeam = 1
				and SL.ScheduledPersonID = @SchedulingpersonID

            SET @FLAllocationsID = @@Identity

			INSERT INTO history
				  (
						historytype,
						attributeid,
						datetime,
						userid,
						history
				  )
			SELECT HT.ID AS historytype,
				   @FLAllocationsID AS attributeid,
				   getutcdate(),
				   @vuserID,
				   'The allocations for '
				   +RIGHT(cast(ixYearWeek as VARCHAR),2) + '/'
				   +LEFT(cast(ixYearWeek as VARCHAR),len(cast(ixYearWeek as VARCHAR))-2)
				   +' created by '
				   +@vname+' On '+ FORMAT(Getdate(),'dd/MM/yyyy HH:mm') +'.'
			  FROM TimeDimension TD
			 INNER JOIN HistoryTypes HT on 1 = 1
			 WHERE HT.HistoryType = 'PublishWeek'
			   AND TD.dDateTime =@DutyDate

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
					AD_CreatedBy,
					AD_CreatedDate
					 )
			  SELECT AL_AllocationsID AS allocationid,
					 'U',
					 ixDayInWeek,
					 dDateTime,
					 dDateTime,
					 dDateTime,
					 dDateTime,
					 dDateTime,
					 7,
					 1,
					 @vuserID,
					 getutcdate()
				FROM TimeDimension TD
			   INNER JOIN Allocations AL on td.ixYearWeek = AL_WeekNumber
			   where AL.AL_AllocationsID = @FLAllocationsID

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
					AD_CreatedBy,
					AD_CreatedDate
					 )
			  SELECT AL_AllocationsID AS allocationid,
					 'Leave',
					 ixDayInWeek,
					 dDateTime,
					 dDateTime,
					 dDateTime,
					 dDateTime,
					 dDateTime,
					 8,
					 1,
					 @vuserID,
					 getutcdate()
				FROM TimeDimension TD
			   INNER JOIN Allocations AL on td.ixYearWeek = AL_WeekNumber
			   where AL.AL_AllocationsID = @FLAllocationsID

		   END

		 IF ( ISNULL(@AllocationsDutyID,0) = 0 )
		  BEGIN

			SELECT @AllocationsDutyID = AD_AllocationsDutyID
			 FROM AllocationsDuties AD
			WHERE AD.AD_AllocationsID = @FLAllocationsID
			  AND AD.AD_DutyDate = @DutyDate
			  AND AD_DutyType = 7

		  END

	  END


	SELECT TOP 1 @SortCode = AP.ASP_SortCode
	  FROM Allocations AL
	 INNER JOIN TimeDimension TD on TD.ixYearWeek = AL.AL_WeekNumber
	 INNER JOIN ScheduledPersonTeam_LINK SL on SL.TeamID = AL.AL_SchedulingTeamID
	 INNER JOIN AllocationsScheduledPersons AP ON AL.AL_AllocationsID = AP.ASP_AllocationsID
											AND SL.ScheduledPersonID = AP.ASP_SchedulingPersonID
	WHERE TD.dDateTime >= SL.StartDate and TD.dDateTime <= SL.EndDate
		AND SL.scheduledType = 1
		AND SL.IsHomeTeam = 1
		AND SL.ScheduledPersonID = @SchedulingpersonID
		AND TD.dDateTime = @DutyDate
		AND ISNULL(SL.SortCode,'') <> ISNULL(AP.ASP_SortCode,'')

	 INSERT INTO AllocationsScheduledPersons(
				 ASP_AllocationsID,
				 ASP_AllocationsDutyID,
				 ASP_SchedulingPersonID,
				 ASP_DutyTeamID,
				 ASP_iDay,
				 ASP_SortCode,
				 ASP_LeaveStatus,
				 ASP_DutyDate,
				 ASP_WIADStatus,
				 ASP_MarkedOverTime,
				 ASP_OverTimeHours,
				 ASP_EDPStatus,
				 ASP_Comments,
				 ASP_CreatedBy,
				 ASP_CreatedDate)
		  SELECT AL_AllocationsID,
				 AD_AllocationsDutyID,
				 sl.ScheduledPersonID,
				 CASE WHEN @IsFreeLancer = 0
					  THEN AL_SchedulingTeamID
					  ELSE NULL END,
				 td.ixDayInWeek,
				 CASE WHEN @SortCode IS NULL THEN SL.SortCode ELSE @SortCode END,
				 0,
				 @DutyDate,
				 CASE WHEN @IsFreeLancer = 1 AND AD_DutyType < 7 
					  THEN 2 ELSE 0 END,
				 0,
				 0,
				 NULL,
				 NULL,
				 @vuserID,
                 getutcdate()
            FROM Allocations AL
		   INNER JOIN ScheduledPersonTeam_LINK SL on SL.TeamID = AL.AL_SchedulingTeamID
		   INNER JOIN TimeDimension TD on TD.ixYearWeek = AL.AL_WeekNumber
		   INNER JOIN AllocationsDuties AD ON 1 = 1
		   WHERE td.dDateTime between sl.StartDate and sl.EndDate
		     AND sl.scheduledType = 1
			 AND sl.IsHomeTeam = 1
		     AND sl.ScheduledPersonID = @SchedulingpersonID
			 AND td.dDateTime = @DutyDate
			 AND AD.AD_AllocationsDutyID = @AllocationsDutyID

			SET @AllocationsSPID = @@Identity

			INSERT INTO AllocationsAddPersons
						(   AAP_AllocationsID,
							AAP_AllocationsSPID,
							AAP_SchedulingPersonID,
							AAP_Status,
							AAP_iDay,
							AAP_DutyDate,
							AAP_SortCode,
							AAP_CreatedBy,
							AAP_CreatedDate )
					 SELECT AL1.AL_AllocationsID,
							AP.ASP_AllocationsSPID,
							AP.ASP_SchedulingPersonID,
							1,
							AP.ASP_iDay,
							AP.ASP_DutyDate,
							SLA.SortCode,
							@vuserID,
							getdate()
					   FROM Allocations AL
					  INNER JOIN ScheduledPersonTeam_LINK SL ON SL.TeamID = AL.AL_SchedulingTeamID
					  INNER JOIN ScheduledPersonTeam_LINK SLA ON SLA.ScheduledPersonID = SL.ScheduledPersonID
					  INNER JOIN AllocationsScheduledPersons AP on AL.AL_AllocationsID = AP.ASP_AllocationsID
															   AND SLA.ScheduledPersonID = ASP_SchedulingPersonID
					  INNER JOIN Allocations AL1 on SLA.TeamID = AL1.AL_SchedulingTeamID and AL.AL_WeekNumber = AL1.AL_WeekNumber
					   WHERE AP.ASP_DutyDate BETWEEN SL.startdate AND SL.enddate
					     AND AP.ASP_DutyDate BETWEEN SLA.startdate AND SLA.enddate
						 AND SL.scheduledtype = 1
						 AND SL.IsHomeTeam = 1
						 AND SLA.scheduledtype = 1
						 AND SLA.IsHomeTeam IN (0,2)
						 AND SLA.IsAvailable = 1
						 AND ap.ASP_AllocationsSPID = @AllocationsSPID
						 AND sl.ScheduledPersonID = @SchedulingpersonID
						 AND AP.ASP_DutyDate  = @DutyDate
						 AND NOT EXISTS ( SELECT 1
											FROM AllocationsAddPersons AAP
										   WHERE AAP.AAP_AllocationsID = AL1.AL_AllocationsID
										     AND AAP.AAP_SchedulingPersonID = AP.ASP_SchedulingPersonID
											 AND AAP.AAP_DutyDate = AP.ASP_DutyDate)

				   UPDATE AAP
					  SET AAP.AAP_AllocationsSPID = asp.ASP_AllocationsSPID
					 from AllocationsScheduledPersons asp
					 inner join AllocationsAddPersons  aap on ASP_SchedulingPersonID = AAP_SchedulingPersonID
														   AND ASP_DutyDate = AAP_DutyDate
					 WHERE asp.ASP_AllocationsSPID = @AllocationsSPID
					   AND aap.AAP_AllocationsSPID IS NULL


						INSERT INTO history
										(
										  historytype,
										  attributeid,
										  datetime,
										  userid,
										  history,
										  HistorySubType
										)
								  SELECT ht.id AS historytype,
									   @AllocationsSPID AS attributeid,
									   al.AL_CreatedDate,
									   AL_CreatedBy,
									   'Created '
									   + ' On ' + FORMAT(AL_CreatedDate,'dd/MM/yyyy HH:mm')
									   + ' By ' + UD_DisplayName
									   + ' assigned to '+@DisplayName
									   + '. Duty: U'
									   +'.',
									   'PH'
							 FROM Allocations AL
							INNER JOIN UserDetails UD on ud.UD_UserID = AL_CreatedBy
							INNER JOIN HistoryTypes HT (Nolock) on 1=1
							WHERE historytype = 'AllocationScheduledPerson'
							  and AL_AllocationsID = @AllocationsID


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