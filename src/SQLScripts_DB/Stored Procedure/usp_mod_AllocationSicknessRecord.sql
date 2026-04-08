USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_mod_AllocationSicknessRecord]    Script Date: 26/12/2025 22:04:28 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER       PROCEDURE [dbo].[usp_mod_AllocationSicknessRecord]
@sicknessid	 int,
@action VARCHAR(20),
@scheduledpersonid	 int,
@staffnumber VARCHAR(50),
@hours INT,
@reasonid INT,
@sickdate DATE,
@currentuserid INT,
@ischecked INT,
@sickstartdate DATE,
@sickenddate DATE,
@checkon VARCHAR(200),
@comment varchar(500),
@allocationid int ,
@synctype int,
@AllocationsSPID int
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

	DECLARE  @err				int,
			 @rows				int,
			 @RowNum			int,
			 @UpdateDateTime	datetime,
			 @strUpdateDateTime varchar(35),
			 @oldcommenttext	VARCHAR(500),
			 @newid				INT,
			 @status			varchar(50),
			 @returnstring		varchar(100),
			 @DutyName			VARCHAR(100),
			 @LeaveType			INT,
			 @NewLeaveType		INT,
			 @DutyType			INT,
			 @ReturnValue		INT,
			 @NetLogin			VARCHAR(15);


	SET @status = 'success'
	SET @returnstring = 'Sickness record has been saved successfully.'
	SET @UpdateDateTime = getutcdate()
	SET @strUpdateDateTime = CONVERT(VARCHAR, @UpdateDateTime, 106) + ' at ' + CONVERT(VARCHAR, @UpdateDateTime, 108)
	SET @newid = 0


    SELECT @NetLogin = UD_NetLogin
	  FROM UserDetails
	WHERE UD_UserID = @currentuserid

  BEGIN TRANSACTION

	if @action = 'insert'
	 Begin

	 		 IF ( ISNULL(@AllocationsSPID,0) = 0 )
			   BEGIN

					EXEC @ReturnValue = usp_CreateScheduledPerson  @allocationid,
													0,
													@scheduledpersonid,
													@sickdate,
													@NetLogin,
													@AllocationsSPID = @AllocationsSPID OUTPUT

					IF ( ISNULL(@ReturnValue,0) > 0 )
					BEGIN
							ROLLBACK TRANSACTION
							SET @status = 'error'
							SET @returnstring = 'Error while creating the scheduling person details.'
							SELECT @status strstatus , @returnstring strreturnstring;
							RETURN 0
					END
					IF (@AllocationsSPID ) = 0
					  BEGIN
							ROLLBACK TRANSACTION
							SET @status = 'error'
							SET @returnstring = 'Error while creating the scheduled person details.'
							SELECT @status strstatus , @returnstring strreturnstring;
							RETURN 0
					  END

			   END

				 SELECT @DutyName           = AD_DutyName,
						@LeaveType          = ASP_LeaveType,
						@DutyType           = AD_DutyType
				   FROM AllocationsDuties AD
				  INNER JOIN AllocationsScheduledPersons ASP on ASP.ASP_AllocationsDutyID = AD_AllocationsDutyID
				  WHERE ASP_AllocationsSPID = @AllocationsSPID;

				SET @NewLeaveType = CASE when @DutyType IN (7,9) AND @hours = 0 THEN 4
										 when @LeaveType IN (3,4) AND @hours = 0 THEN 4
										 when @DutyName = 'U' AND @hours > 0 THEN 3
										 when @DutyName like '-%' AND @hours = 0 THEN 5
										 when @DutyType NOT IN (7,9) AND @hours = 0 THEN 4
										 when @DutyType NOT IN (7,9) THEN 3
										 ELSE 4
								     END

			SELECT @newid = ID
			  FROM LeaveApplications
			 WHERE dDate = @sickdate
			   AND SchedulingPersonID = @scheduledpersonid
			   AND ISNULL(LeaveTypeID,0) IN ( 3,4,5) 

		  IF ( ISNULL(@newid,0) = 0 )
		   BEGIN
			  INSERT INTO LeaveApplications(  SchedulingPersonID,
											  AllocationsSPID,
											  Totalhrs,ReasonsId,
											  isChecked,dDate,
											  Comments,
											  Approved, Deleted,
											  LeaveTypeID,CreatedBy,
											  Created,LastModDate,LastModBy)
			  VALUES
				   (@scheduledpersonid,@AllocationsSPID,@hours,@reasonid,@ischecked,
					@sickdate,@comment,1,0,@NewLeaveType,
					@currentuserid,@UpdateDateTime,@UpdateDateTime,@currentuserid);

				SET @newid = @@IDENTITY

		   END
		 ELSE
		   BEGIN

		     UPDATE LeaveApplications
				SET AllocationsSPID = @AllocationsSPID,
					Totalhrs = @hours,
					ReasonsId = @reasonid,
					isChecked = @ischecked,
					Comments = @comment,
					Approved = 1, Deleted = 0,
					LeaveTypeID = @NewLeaveType,
					LastModBy = @currentuserid,
					LastModDate = @UpdateDateTime
			  WHERE ID = @newid

		   END

			--CHECK ERROR
			SELECT @err = @@ERROR, @rows = @@ROWCOUNT

					IF @err <> 0
						BEGIN
							ROLLBACK TRANSACTION
							SET @status = 'error'
							SET @returnstring = 'Error saving the Sickneess .'
							SELECT @status strstatus , @returnstring strreturnstring;
							RETURN 0
						END
					IF @rows = 0
						BEGIN
							ROLLBACK TRANSACTION
							SET @status = 'error'
							SET @returnstring = 'Error saving the Sickneess .'
							SELECT @status strstatus , @returnstring strreturnstring;
							RETURN 0
						END
	End
	Else
		BEGIN
		-- update the record
			SET @newid = @sicknessid

			-- update whole record
					IF @action = 'update'
						BEGIN

							 SELECT @DutyName           = AD_DutyName,
									@LeaveType          = ASP_LeaveType,
									@DutyType           = AD_DutyType
							   FROM AllocationsDuties AD
							  INNER JOIN AllocationsScheduledPersons ASP on ASP.ASP_AllocationsDutyID = AD_AllocationsDutyID
							  WHERE ASP_AllocationsSPID = @AllocationsSPID;

							SET @NewLeaveType = CASE when @DutyType IN ( 8,11,12) AND @hours = 0 THEN 4
													 when @DutyType IN (7,9) AND @hours = 0 THEN 4
													 when @LeaveType IN (3,4) AND @hours = 0 THEN 4
													 when @DutyName = 'U' AND @hours > 0 THEN 3
													 when @DutyName like '-%' AND @hours = 0 THEN 5
													 when @DutyType NOT IN (7,9) AND @hours = 0 THEN 4
													 when @DutyType NOT IN (7,9) THEN 3
												 END

								UPDATE LeaveApplications
										SET isChecked = @ischecked,
											Totalhrs = @hours,
										    ReasonsId = @reasonid,
											LeaveTypeID = @NewLeaveType,
											LastModBy =@currentuserid  ,
											LastModDate =@UpdateDateTime
									  WHERE ID = @sicknessid

								SET @returnstring = 'Sickness record has been saved successfully.'


						END

		END



	IF @synctype = 1 OR @synctype = 2
		BEGIN
			UPDATE LeaveApplications
				SET ReasonsId = @reasonid ,
					LastModBy = @currentuserid  ,
					LastModDate = @UpdateDateTime
				WHERE SchedulingPersonID = @scheduledpersonid
					and ID IN (Select ID
								 from LeaveApplications
								where dDate >= @sickstartdate
								  and dDate <= @sickenddate
								  and SchedulingPersonID = @scheduledpersonid
								  and LeaveTypeID IN (3,4,5)
								)
		END




	if @checkon = 'on'
	BEGIN
		UPDATE LeaveApplications
		   SET Comments = @comment
		 WHERE ID IN (Select ID
						from LeaveApplications
					   where dDate >= @sickstartdate
						 and dDate <= @sickenddate
						 and SchedulingPersonID = @scheduledpersonid
						 and LeaveTypeID IN (3,4,5)
					  )
		SET @status = 'success'
	SET @returnstring = 'Sickness record has been saved successfully.'
	END

	if @checkon = 'off'
	BEGIN

		UPDATE LeaveApplications
		   SET Comments = @comment
		 where ID = @newid

		SET @status = 'success'
		SET @returnstring = 'Sickness record has been saved successfully.'

	END


	SELECT @status strstatus , @returnstring strreturnstring;
	COMMIT TRANSACTION


END