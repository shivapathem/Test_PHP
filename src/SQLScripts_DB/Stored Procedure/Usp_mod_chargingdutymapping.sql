USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_mod_ChargingDutyMapping]    Script Date: 14/11/2025 16:04:03 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

CREATE OR ALTER PROCEDURE [dbo].[usp_mod_ChargingDutyMapping]
  @ChargingId        INT,
  @ChargeCodeId      INT,
  @ActivityCodeId    INT,
  @MasterDutyId      INT,
  @AllocationId      INT,
  @ChargeFrom        INT,
  @Quantity          DECIMAL(18, 2),
  @UnitPrice         DECIMAL(18, 2),
  @Comments          VARCHAR(100),
  @Contact           VARCHAR(50),
  @Telephone         VARCHAR(50),
  @IsActual          INT,
  @IsPrefilled       BIT,
  @ChargingDate      VARCHAR(50),
  @scheduledPersonId INT,
  @staffDetailsId    INT,
  @CreatedBy         INT,
  @ActionType        VARCHAR(10),
  @ChargingTeamID	 INT
AS
  BEGIN
      -- SET NOCOUNT ON added to prevent extra result sets from
      -- interfering with SELECT statements.
      SET NOCOUNT ON;

      DECLARE @CreatedDate      DATETIME,
              @StrStatus        VARCHAR(max),
              @IntStatus        INT,
              @SentToFinance    INT,
              @ChargingDutyDate DATETIME,
              @UserName         VARCHAR(100),
              @HistoryMsg       VARCHAR(max),
			  @Chargingstatus	INT;

      SET @IntStatus = 1;
      SET @SentToFinance = 0
      SET @StrStatus = 'Success';
      SET @ChargingDutyDate = CONVERT(DATETIME, @ChargingDate + ' 00:00:00', 103);

      SELECT @UserName = UD_DisplayName
      FROM  UserDetails
      WHERE  UD_UserID = @CreatedBy;

      IF ( @ActionType = 'Insert' )
        BEGIN
            --Allocations
            -- Insert statements for procedure here
            INSERT INTO ChargingDutyMapping_Link
                        (EstabCodeId,
                         ActivityCodeId,
                         MasterDutyId,
                         AllocationId,
                         ChargeCodeId,
                         Quantity,
                         UnitPrice,
                         Comments,
                         Contact,
                         Telephone,
                         IsActual,
                         ChargingDutyDate,
                         IsSentToFinance,
                         PersonId,
                         StaffId,
						 SChedulingTeamId,
                         CreatedBy,
                         CreatedDate,
                         ModifiedBy,
                         ModifiedDate)
            VALUES      (@ChargeCodeId,
                         @ActivityCodeId,
                         @MasterDutyId,
                         @AllocationId,
                         @ChargeFrom,
                         @Quantity,
                         @UnitPrice,
                         @Comments,
                         @Contact,
                         @Telephone,
                         @IsActual,
                         @ChargingDutyDate,
                         @SentToFinance,
                         @scheduledPersonId,
                         @staffDetailsId,
						 @ChargingTeamID,
                         @CreatedBy,
                         Getutcdate(),
                         @CreatedBy,
                         Getutcdate());

            SET @ChargingId = Scope_identity();
            /* Create history for newly created record start*/
            SET @HistoryMsg = 'New Charging record is created by '
                              + @UserName + ' on '
                              + Format(Getdate(), 'dd/MM/yyyy') + ' '
                              + Format(Getdate(), 'HH:mm');

			SELECT @Chargingstatus =  case
				when sum(case when CL.IsActual = 2 then 2 else 1 end) = (2 * count(CL.ChargingId)) and count(CL.ChargingId) > 0 then 4
				when sum(case when CL.IsActual = 0 then 0 else 1 end) = count(CL.ChargingId) and count(CL.ChargingId) > 0 then 3
				when sum(case when CL.IsActual = 0 then 0 else 1 end) = 0 and count(CL.ChargingId) > 0 then 2
				when sum(case when CL.IsActual = 0 then 0 else 1 end) < count(CL.ChargingId) and count(CL.ChargingId) > 0 then 1
				else 5 end
		   from ChargingDutyMapping_Link CL
		  INNER JOIN AllocationsScheduledPersons AL ON AL.ASP_AllocationsSPID = CL.AllocationId
		  WHERE ASP_AllocationsSPID = @AllocationId
		  GROUP by CL.AllocationId, CL.MasterDutyId

		  UPDATE AllocationsScheduledPersons
		     SET ASP_ChargingStatus = @Chargingstatus,
				 ASP_ChargingTeamID = @ChargingTeamID
		   WHERE ASP_AllocationsSPID = @AllocationId

            INSERT INTO History
                        (HistoryType,
                         UserID,
                         History,
                         datetime,
                         AttributeID,
                         HistorySubType)
            SELECT       ht.id AS historytype,
                         @CreatedBy,
                         @HistoryMsg,
                         Getdate(),
                         @AllocationId,
                         'PH'
					FROM HistoryTypes ht
				   WHERE ht.historytype = 'AllocationScheduledPerson'

            /* Create history for newly created record end*/
            SET @IntStatus = 1;
            SET @StrStatus = '001';

        END
      ELSE IF ( @ActionType = 'Delete' )
        BEGIN
            IF EXISTS (SELECT 1
                       FROM   [dbo].[ChargingDutyMapping_Link]
                       WHERE  ChargingId = @ChargingId
                              AND IsSentToFinance = 1)
              BEGIN
                  SET @IntStatus = 0;
                  SET @StrStatus = '026';
              END
            ELSE
              BEGIN

                  DELETE FROM [dbo].[ChargingDutyMapping_Link]
                  WHERE  ChargingId = @ChargingId;

                  DELETE FROM [dbo].[PrefilledChargeDetails_Link]
                  WHERE  ChargingId = @ChargingId
                         AND UserId = @CreatedBy;

				  UPDATE AllocationsScheduledPersons
					 SET ASP_ChargingStatus = 0,
						 ASP_ChargingTeamID = NULL
				   WHERE ASP_AllocationsSPID = @AllocationId

                  /* To create history of charging delete functionality start*/
                  SET @HistoryMsg = 'Charging record is deleted by '
                                    + @UserName + ' on '
                                    + Format(Getdate(), 'dd/MM/yyyy') + ' '
                                    + Format(Getdate(), 'HH:mm');

                  INSERT INTO History
                              (HistoryType,
                               UserID,
                               History,
                               datetime,
                               AttributeID,
                               HistorySubType)
                  SELECT      ht.id AS historytype,
                               @CreatedBy,
                               @HistoryMsg,
                               Getdate(),
                               @AllocationId,
                               'PH'
					FROM HistoryTypes ht
				   WHERE ht.historytype = 'AllocationScheduledPerson'

                  /* To create history of charging delete functionality end*/
                  SET @IntStatus = 1;
                  SET @StrStatus = '001';
              END
        END
      ELSE
        BEGIN
            --Add edit or select here
            IF EXISTS(SELECT 1
                      FROM   [dbo].[ChargingDutyMapping_Link]
                      WHERE  ChargingId = @ChargingId
                             AND IsSentToFinance = 1)
              BEGIN
                  SET @IntStatus = 0;
                  SET @StrStatus = '032';
              END
            ELSE IF EXISTS(SELECT 1
                      FROM   [dbo].[ChargingDutyMapping_Link]
                      WHERE  MasterDutyId = @MasterDutyId
                             AND AllocationId = @AllocationId
                             AND ChargeCodeId = @ChargeFrom
                             AND ActivityCodeId = @ActivityCodeId
                             AND PersonId = @scheduledPersonId
                             AND Quantity = @Quantity
                             AND UnitPrice = @UnitPrice
                             AND Comments = @Comments
                             AND Contact = @Contact
                             AND Telephone = @Telephone
                             AND IsActual = @IsActual)
              BEGIN
                  SET @IntStatus = 0;
                  SET @StrStatus = '031';
              END
            ELSE
              BEGIN
                  IF NOT EXISTS(SELECT 1
                                FROM   ChargingDutyMapping_Link
                                WHERE  ChargingId = @ChargingId
                                       AND IsActual = @IsActual)
                    BEGIN
                        DECLARE @IsActualOld INT

                        SELECT @IsActualOld = IsActual
                        FROM   ChargingDutyMapping_Link
                        WHERE  ChargingId = @ChargingId

                        SET @HistoryMsg = 'Charging status changed from ' + CASE
                                          WHEN @IsActualOld = 0 THEN +'Provisional'
                                          WHEN @IsActualOld = 1 THEN 'Actual'
                                          ELSE 'Hold'
                                          END
                                          +' to '
                                          + CASE WHEN @IsActual = 0 THEN +'Provisional'
                                          WHEN @IsActual = 1 THEN 'Actual'
										  ELSE 'Hold' END + ' updated by ' +
                                          @UserName + ' on '
                                          + Format(Getdate(), 'dd/MM/yyyy HH:mm')
                    END
                  ELSE
                    BEGIN
                        SET @HistoryMsg = 'Charging record is updated by '
                                          + @UserName + ' on '
                                          + Format(Getdate(), 'dd/MM/yyyy HH:mm')
                    END

                  UPDATE ChargingDutyMapping_Link
                  SET    ActivityCodeId = @ActivityCodeId,
                         ChargeCodeId = @ChargeFrom,
                         Quantity = @Quantity,
                         UnitPrice = @UnitPrice,
                         Comments = @Comments,
                         Contact = @Contact,
                         Telephone = @Telephone,
                         IsActual = @IsActual,
                         ModifiedBy = @CreatedBy,
                         ModifiedDate = Getutcdate()
                  WHERE  ChargingId = @ChargingId

					SELECT @Chargingstatus =  case
						when sum(case when CL.IsActual = 2 then 2 else 1 end) = (2 * count(CL.ChargingId)) and count(CL.ChargingId) > 0 then 4
						when sum(case when CL.IsActual = 0 then 0 else 1 end) = count(CL.ChargingId) and count(CL.ChargingId) > 0 then 3
						when sum(case when CL.IsActual = 0 then 0 else 1 end) = 0 and count(CL.ChargingId) > 0 then 2
						when sum(case when CL.IsActual = 0 then 0 else 1 end) < count(CL.ChargingId) and count(CL.ChargingId) > 0 then 1
						else 5 end
				   from ChargingDutyMapping_Link CL
				  INNER JOIN AllocationsScheduledPersons AL ON AL.ASP_AllocationsSPID = CL.AllocationId
				  WHERE ASP_AllocationsSPID = @AllocationId
				  GROUP by CL.AllocationId, CL.MasterDutyId

				  UPDATE AllocationsScheduledPersons
					 SET ASP_ChargingStatus = @Chargingstatus
				   WHERE ASP_AllocationsSPID = @AllocationId

                  /* To create history of charging update functionality start*/
                  INSERT INTO History
                              (HistoryType,
                               UserID,
                               History,
                               datetime,
                               AttributeID,
                               HistorySubType)
                  SELECT      ht.id AS historytype,
                               @CreatedBy,
                               @HistoryMsg,
                               Getdate(),
                               @AllocationId,
                               'PH'
					FROM HistoryTypes ht
				   WHERE ht.historytype = 'AllocationScheduledPerson'

                  /* To create history of charging update functionality end*/
                  SET @IntStatus = 1;
                  SET @StrStatus = '001';
              END
        END

      SELECT @IntStatus IntStatus,
             @StrStatus StrStatus;
  END